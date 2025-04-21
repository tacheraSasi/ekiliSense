<?php 
require_once "../../config.php";
require_once "../../../app/api.php";

if (empty($_SESSION['school_uid'])) {
    echo 'No school in session.';
    exit;
}


$uniqueId  = $_SESSION['school_uid'];

$school_unique_id = $_SESSION['School_uid'];
$country = trim($_POST['country'] ?? '');

$school = Api::getSchoolByUniqueId($uniqueId);
if (empty($school['id'])) {
    echo $school['message'] ?? 'School not found.';
    exit;
}

$update = Api::updateSchool($school['id'], [
    'address' => $country,
]);
if (isset($update['status']) && $update['status'] === 'error') {
    echo $update['message'] ?? 'Update failed.';
    exit;
}

if (! empty($update['id'])) {
    echo 'success';
} else {
    echo $update['message'] ?? 'Update failed.';
}
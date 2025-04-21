<?php
session_start();
require_once "../../config.php";
require_once "../../../app/api.php";

if (empty($_SESSION['School_uid'])) {
    echo 'No school in session.';
    exit;
}

$uniqueId  = $_SESSION['School_uid'];
$newPhone  = trim($_POST['phone'] ?? '');

if ($newPhone === '') {
    echo 'Phone number is required.';
    exit;
}

// 1) Lookup school by uniqueId
$school = Api::getSchoolByUniqueId($uniqueId);
if (empty($school['id'])) {
    echo $school['message'] ?? 'School not found.';
    exit;
}

// 2) Update its phone
$update = Api::updateSchool($school['id'], [
    'phoneNumber' => $newPhone,
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

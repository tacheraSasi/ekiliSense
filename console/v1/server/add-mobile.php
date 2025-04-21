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
$school = Api::request('GET', "/schools/unique/{$uniqueId}");
if (empty($school['id'])) {
    echo $school['message'] ?? 'School not found.';
    exit;
}

// 2) Update its phone
$update = Api::request('PUT', "/schools/{$school['id']}", [
    'phoneNumber' => $newPhone,
]);

if (! empty($update['id'])) {
    echo 'success';
} else {
    echo $update['message'] ?? 'Update failed.';
}

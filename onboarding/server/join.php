<?php
session_start();
require_once "../../config.php";
require_once "../../app/api.php";

$instituteName   = trim($_POST['institute-name']   ?? '');
$email           = trim($_POST['email']            ?? '');
$phoneNumber     = trim($_POST['phone']            ?? '0');
$password        = $_POST['password']              ?? '';
$confirmPassword = $_POST['confirm-password']      ?? '';

if ($instituteName === '' || $email === '' || $password === '') {
    echo 'All input fields are required!';
    exit;
}

if ($password !== $confirmPassword) {
    echo 'Password does not match!';
    exit;
}

$response = Api::createSchool([
    'schoolName'   => $instituteName,
    'address'      => $country,
    'email'        => $email,
    'phoneNumber'  => $phoneNumber,
    'adminPassword'=> $password,
]);

if (isset($response['schoolUniqueId'])) {
    $response = Api::login($email, $password);
    if (isset($response['token'])) {
        $_SESSION['token'] = $response['token'];
        $_SESSION['user'] = $response['user'];
        $_SESSION['school_uid'] = $_SESSION['user']['schoolUniqueId'];
    } else {
        echo 'Login failed!';
        exit;
    }
    echo 'success';
} else {
    echo $response['message'] ?? 'Something went wrong. Please try again.';
}

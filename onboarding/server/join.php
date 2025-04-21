<?php
require_once "../../app/api.php";

$instituteName   = trim($_POST['institute-name']   ?? '');
$email           = trim($_POST['email']            ?? '');
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
    'address'      => 'Tanzania, Dar es Salaam',
    'email'        => $email,
    'phoneNumber'  => '0000000000',
    'adminPassword'=> $password,
]);

if (isset($response['schoolUniqueId'])) {
    $response = Api::login($email, $password);
    if (isset($response['token'])) {
        $_SESSION['token'] = $response['token'];
        $_SESSION['user'] = $response['user'];
        $_SESSION['school_uid'] = $response['school']['uniqueId'];
    } else {
        echo 'Login failed!';
        exit;
    }
    echo 'success';
} else {
    echo $response['message'] ?? 'Something went wrong. Please try again.';
}

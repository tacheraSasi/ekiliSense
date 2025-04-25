<?php
require_once "../../app/api.php";

// Initialize login attempts counter
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

if ($_SESSION['login_attempts'] >= 3) {
    echo 'Too many attempts! Please try again later';
    exit;
}

if ($email === '' || $password === '') {
    echo 'All input fields are required!';
    exit;
}

$response = Api::login($email, $password);
var_dump($response);

if (!empty($response['token'])) {
    // successful login
    $_SESSION['login_attempts'] = 0;
    $_SESSION['token']          = $response['token'];
    $_SESSION['user']           = $response['user'];
    $_SESSION['school_uid']     = $response['user']['school']['uniqueId'] ?? null;//Will fix this later
    echo 'success';
    exit;
}

// failed login
$_SESSION['login_attempts']++;

if ($_SESSION['login_attempts'] >= 3) {
    echo 'Maximum login attempts reached. Please try again later.';
} else {
    $msg = $response['message'] ?? 'Invalid credentials';
    $left = 3 - $_SESSION['login_attempts'];
    echo "{$msg}. Attempts left: {$left}";
}

<?php 
session_start();
require '../config.php';  
// require '../parseEnv.php';  already requiresd in config.php
require '../vendor/autoload.php';

$client = new Google_Client();
$client->setApplicationName('ekiliSense');
$client->setClientId(getenv("GOOGLE_CLIENT_ID"));
$client->setClientSecret(getenv("GOOGLE_CLIENT_SECRET"));
$client->setRedirectUri(getenv("GOOGLE_REDIRECT_URI"));
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->addScope(Google_Service_Oauth2::USERINFO_PROFILE); // Getting user's profile info
$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);   // Getting user's email

// Handle the OAuth 2.0 server response
if (!isset($_GET['code'])) {
    // Generating the URL for the Google OAuth consent screen
    $authUrl = $client->createAuthUrl();

    // Redirecting the user to Google's OAuth 2.0 server (consent screen)
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
    
}else{
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Get user details from Google
    $oauth2Service = new Google_Service_Oauth2($client);
    $googleUserInfo = $oauth2Service->userinfo->get();
    
    // Extract the relevant details
    $google_id = $googleUserInfo->id;
    $name = $googleUserInfo->name;
    $email = $googleUserInfo->email;
    $picture = $googleUserInfo->picture;
    $phone = ''; // Can be empty, you will need to request access for phone number

    // Save the user's details and tokens to the database
    $access_token = $client->getAccessToken()['access_token'];
    $refresh_token = $client->getRefreshToken();
    $expires_in = $client->getAccessToken()['expires_in'];
    $token_created_at = date('Y-m-d H:i:s', $client->getAccessToken()['created']);

    // Insert or update teacher details in the database
    $query = "INSERT INTO teachers_google 
              (google_id, name, email, phone, picture_url, access_token, refresh_token, expires_in, token_created_at)
              VALUES ('$google_id', '$name', '$email', '$phone', '$picture', '$access_token', '$refresh_token', $expires_in, '$token_created_at')
              ON DUPLICATE KEY UPDATE 
              name = '$name',
              picture_url = '$picture',
              access_token = '$access_token', 
              refresh_token = '$refresh_token',
              expires_in = $expires_in,
              token_created_at = '$token_created_at'";
              
    mysqli_query($conn, $query);

    // Redirecting back 

    // if(isset($_SERVER['HTTP_REFERER'])){
    //     $refferer = $_SERVER['HTTP_REFERER'];
    //     header("location:$refferer");
    // }else{
    //     header('Location: ../console/class/teacher/');
    // }
    header('Location: ../console/class/teacher/');
    exit();
}

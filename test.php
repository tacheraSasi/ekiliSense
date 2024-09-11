<?php
// require "vendor/autoload.php";
// use ekilie\EkiliRelay;

// $ekilirelay = new EkiliRelay("");
// $res = $ekilirelay->sendEmail("","","","");
// print_r($res);

require __DIR__ . '/vendor/autoload.php'; // Loads the Twilio SDK

use Twilio\Rest\Client;

// Twilio credentials
$sid = 'ACf697c759db15e70115bc1cdcaf72bc56'; // Replace with your Twilio Account SID
$token = '1ea667794a42522ec0ce1575bfef8b6c'; // Replace with your Twilio Auth Token
$twilioNumber = '+12089242409'; // Replace with your Twilio phone number

// The phone number you want to send the message to
$recipientNumber = '+255686477074'; // Recipient's phone number

// Create the Twilio client
$client = new Client($sid, $token);

// Send the message
$message = $client->messages->create(
    $recipientNumber, // To the recipient number
    [
        'from' => $twilioNumber, // From your Twilio number
        'body' => 'Hello, this is a message from Twilio!\n love your so much budddy' // Message content
    ]
);

// Check if the message was sent successfully
if ($message->sid) {
    echo "Message sent successfully with SID: " . $message->sid;
} else {
    echo "Failed to send message.";
}
?>


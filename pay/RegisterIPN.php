<?php 
require 'acesstoken.php';
require '../vendor/autoload.php'; // Ensure Guzzle is autoloaded

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Define the environment (sandbox or live)
if (APP_ENVIROMENT == 'sandbox') {
    $ipnRegistrationUrl = "https://cybqa.pesapal.com/pesapalv3/api/URLSetup/RegisterIPN"; // Sandbox URL
} elseif (APP_ENVIROMENT == 'live') {
    $ipnRegistrationUrl = "https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN"; // Live URL
} else {
    echo "Invalid APP_ENVIROMENT";
    exit;
}

// Define headers for the request
$headers = [
    "Accept" => "application/json",
    "Content-Type" => "application/json",
    "Authorization" => "Bearer $token"
];

// Define the data to send with the POST request
$data = [
    "url" => "https://sense.ekilie.com/pay/pin.php", 
    "ipn_notification_type" => "POST"
];

try {
    // Create a Guzzle HTTP client instance
    $client = new Client();

    // Send the POST request
    $response = $client->post($ipnRegistrationUrl, [
        'headers' => $headers,
        'json' => $data, // Guzzle will automatically JSON-encode the data
    ]);

    // Get the response body and HTTP status code
    $responseBody = $response->getBody()->getContents();
    $responseCode = $response->getStatusCode();

    // Decode the response JSON to a PHP object
    $responseData = json_decode($responseBody);

    if ($responseCode == 200 && isset($responseData->ipn_id)) {
        $ipn_id = $responseData->ipn_id;
        $ipn_url = $responseData->url;

        // Output or use the IPN registration details
        // echo "IPN ID: " . $ipn_id . "\n";
        // echo "IPN URL: " . $ipn_url;
    } else {
        echo "Error: Failed to register IPN. HTTP Status Code: " . $responseCode . "\n";
        echo "Response: " . $responseBody;
    }

} catch (RequestException $e) {
    // Handle any Guzzle request errors
    echo "Error: " . $e->getMessage();
}

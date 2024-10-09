<?php 
session_start();
require '../parseEnv.php'; 
require '../vendor/autoload.php'; 
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Load environment variables
parseEnv('../.env');

// Define the environment (sandbox or live)
define('APP_ENVIROMENT', 'live'); // Change this to 'live' in production

// Set the API URL based on the environment
if (APP_ENVIROMENT == 'sandbox') {
    $apiUrl = "https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken"; // Sandbox URL
} elseif (APP_ENVIROMENT == 'live') {
    $apiUrl = "https://pay.pesapal.com/v3/api/Auth/RequestToken"; // Live URL
} else {
    echo "Invalid APP_ENVIROMENT";
    exit;
}

// Retrieve consumer credentials from environment
$consumerKey = getenv("CUSTOMER_KEY");
$consumerSecret = getenv("CUSTOMER_SECRET");

if (!$consumerKey || !$consumerSecret) {
    echo "Error: Consumer Key or Secret not found!";
    exit;
}

try {
    // Create a Guzzle HTTP client instance
    $client = new Client();

    // Send the POST request to the Pesapal API
    $response = $client->post($apiUrl, [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret
        ]
    ]);

    // Get the response body and decode the JSON
    $responseBody = $response->getBody();
    $data = json_decode($responseBody);

    // Check if the request was successful
    if ($response->getStatusCode() == 200 && isset($data->token)) {
        $token = $data->token;
        // echo "Token: " . $token;
    } else {
        // Handle unexpected response
        echo "Error: Failed to retrieve token. Response: " . $responseBody;
    }
} catch (RequestException $e) {
    // Handle Guzzle exceptions (network issues, server errors, etc.)
    echo "Error: " . $e->getMessage();
}

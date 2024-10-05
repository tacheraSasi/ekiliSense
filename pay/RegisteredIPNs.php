<?php 
require 'acesstoken.php'; 
require '../vendor/autoload.php'; 

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Define the environment (sandbox or live)
if (APP_ENVIROMENT == 'sandbox') {
    $getIpnListUrl = "https://cybqa.pesapal.com/pesapalv3/api/URLSetup/GetIpnList"; // Sandbox URL
} elseif (APP_ENVIROMENT == 'live') {
    $getIpnListUrl = "https://pay.pesapal.com/v3/api/URLSetup/GetIpnList"; // Live URL
} else {
    echo "Invalid APP_ENVIROMENT";
    exit;
}

try {
    // Create a Guzzle HTTP client instance
    $client = new Client();

    // Send the GET request to fetch IPN list
    $response = $client->get($getIpnListUrl, [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token  // Pass the token for authorization
        ]
    ]);

    // Get the response body and output it
    echo $responseBody = $response->getBody();

    // You can also retrieve the HTTP response code (e.g., 200, 400, etc.)
    $responseCode = $response->getStatusCode();
} catch (RequestException $e) {
    // Handle any errors from Guzzle
    echo "Error: " . $e->getMessage();
}

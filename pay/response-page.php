<?php
require 'acesstoken.php';
require '../vendor/autoload.php'; // Ensure Guzzle is autoloaded

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Retrieve the tracking ID and merchant reference from the URL query parameters
$OrderTrackingId = $_GET['OrderTrackingId'];
$OrderMerchantReference = $_GET['OrderMerchantReference'];

// Determine the transaction status URL based on the environment
if (APP_ENVIROMENT == 'sandbox') {
    $getTransactionStatusUrl = "https://cybqa.pesapal.com/pesapalv3/api/Transactions/GetTransactionStatus?orderTrackingId=$OrderTrackingId";
} elseif (APP_ENVIROMENT == 'live') {
    $getTransactionStatusUrl = "https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus?orderTrackingId=$OrderTrackingId";
} else {
    echo "Invalid APP_ENVIROMENT";
    exit;
}

// Prepare the request headers
$headers = [
    "Accept" => "application/json",
    "Content-Type" => "application/json",
    "Authorization" => "Bearer $token"
];

try {
    // Create a Guzzle HTTP client instance
    $client = new Client();

    // Send the GET request to retrieve the transaction status
    $response = $client->get($getTransactionStatusUrl, [
        'headers' => $headers
    ]);

    // Get the response body and HTTP status code
    $responseBody = $response->getBody()->getContents();
    $responseCode = $response->getStatusCode();

    // Output the response for debugging or processing
    if ($responseCode == 200) {
        // If successful, output the response body
        echo "Transaction Status Response: \n";
        echo $responseBody;
    } else {
        // Handle error response
        echo "Error: Failed to retrieve transaction status. HTTP Status Code: " . $responseCode . "\n";
        echo "Response: " . $responseBody;
    }

} catch (RequestException $e) {
    // Handle any Guzzle request errors
    echo "Error: " . $e->getMessage();
}

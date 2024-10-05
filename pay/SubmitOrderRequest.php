<?php

// Include file to handle IPN (Instant Payment Notification) registration.
include 'RegisterIPN.php';

use GuzzleHttp\Client;  // Importing the Guzzle HTTP client

// Autoload the dependencies (make sure Guzzle is installed via Composer).
require '../vendor/autoload.php';

// Generate a unique merchant reference using a UUID, prefixed with 'ekilie-payment-'.
$merchantreference = 'ekilie-payment-' .rand(time(), 100000). uniqid();

// echo $merchantreference;

// Customer information
$phone = "0686477074";  // Customer's phone number
$amount = 100.00;       // Amount to be charged (ensure this is a valid float)
$callbackurl = "https://sense.ekilie.com/pay/response-page.php";  // Callback URL for payment response
$branch = "ekiliSense"; // Branch name, could represent different business branches
$first_name = "Tachera";  // Customer's first name
$middle_name = "W";       // Customer's middle name
$last_name = "Sasi";      // Customer's last name
$email_address = "support@ekilie.com";  // Customer's email address

// Determine the correct URL to send the order request based on the environment (sandbox or live).
if (APP_ENVIROMENT == 'sandbox') {
    // Sandbox URL for testing purposes
    $submitOrderUrl = "https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest";
} elseif (APP_ENVIROMENT == 'live') {
    // Live URL for real transactions
    $submitOrderUrl = "https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest";
} else {
    // If the environment is neither 'sandbox' nor 'live', display an error message and exit.
    echo "Invalid APP_ENVIROMENT";
    exit;
}

// Set up the headers for the request. The 'Authorization' header requires the bearer token.
// The token must be fetched from the Pesapal API (as you did earlier) and should be passed here.
$headers = [
    'Accept' => 'application/json',  // Expect a JSON response from Pesapal
    'Content-Type' => 'application/json',  // Sending JSON data
    'Authorization' => 'Bearer ' . $token  // Include the bearer token for authentication
];

// Create the payload (data) that will be sent with the request to Pesapal.
// This includes all order information like amount, customer details, and callback URL.
$data = [
    'id' => $merchantreference,   // Unique merchant reference ID for tracking this order
    'currency' => 'TZS',            // Currency of the transaction (Tanzanian Shilling)
    'amount' => $amount,            // The amount to be charged
    'description' => 'subcription to ekiliSense premium',  // Description of the payment
    'callback_url' => $callbackurl,  // URL where payment result will be sent
    'notification_id' => $ipn_id,   // IPN ID (used for Instant Payment Notification handling)
    'branch' => $branch,            // Optional, indicates which branch is processing the payment
    'billing_address' => [       // Billing address information for the customer
        'email_address' => $email_address,   // Customer's email address
        'phone_number' => $phone,            // Customer's phone number
        'country_code' => 'TZ',                // Country code (Tanzania)
        'first_name' => $first_name,         // Customer's first name
        'middle_name' => $middle_name,       // Customer's middle name
        'last_name' => $last_name,           // Customer's last name
        'line_1' => 'Pesapal Limited',         // Address line 1 (can be the company or individual's address)
        'line_2' => '',                        // Address line 2 (optional)
        'city' => '',                          // City (optional)
        'state' => '',                         // State (optional)
        'postal_code' => '',                   // Postal code (optional)
        'zip_code' => ''                       // Zip code (optional)
    ]
];

// Initialize the Guzzle client
$client = new Client();

try {
    // Send the request using Guzzle, passing the payload and headers
    $response = $client->post($submitOrderUrl, [
        'headers' => $headers,
        'json' => $data // Send the data as JSON
    ]);

    // Capture the response body
    $responseBody = $response->getBody();
    $responseCode = $response->getStatusCode();

    // Check if the response was successful (HTTP 200 OK)
    if ($responseCode == 200) {
        // Decode the JSON response from Pesapal into a PHP array/object
        $responseData = json_decode($responseBody);

        // Check if there's an 'order_tracking_id' in the response
        if (isset($responseData->order_tracking_id)) {
            // Extract the redirect URL from the response
            $redirectUrl = $responseData->redirect_url;

            // Display the iframe with the redirect URL so that the customer can complete the payment
            echo "<iframe src='$redirectUrl' width='100%' height='100%' frameborder='0' style='margin:auto'></iframe>";
        } else {
            // If order_tracking_id is missing, print an error message
            echo "Error: Order tracking ID not found in the response.";
        }
    } else {
        // If the HTTP response code is not 200, print an error message with the response code
        echo "Error: Failed to submit the order. HTTP response code: $responseCode";
    }
} catch (Exception $e) {
    // Handle exceptions (e.g., network issues, server errors)
    echo "Error: " . $e->getMessage();
}


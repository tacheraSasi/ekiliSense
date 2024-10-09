<?php

include '../config.php';  
include 'RegisterIPN.php';

use GuzzleHttp\Client;  // Importing the Guzzle HTTP client

// Autoload the dependencies (make sure Guzzle is installed via Composer).
require '../vendor/autoload.php';

// Generate a unique merchant reference using a UUID, prefixed with 'ekilie-payment-'.
$merchantreference = 'ekilie-payment-' . rand(time(), 100000) . uniqid();

// Customer information for recurring payment
$phone = "0686477074";  // Customer's phone number
$amount = 100.00;       // Amount to be charged (ensure this is a valid float)
$callbackurl = "https://sense.ekilie.com/pay/response-page.php";  // Callback URL for payment response
$branch = "ekiliSense"; // Branch name, could represent different business branches
$first_name = "Tachera";  // Customer's first name
$middle_name = "W";       // Customer's middle name
$last_name = "Sasi";      // Customer's last name
$email_address = "support@ekilie.com";  // Customer's email address

// Subscription interval for recurring payments (monthly, yearly, etc.)
$interval = "monthly";  // This can be 'monthly', 'yearly', or customized

// Determine the correct URL to send the order request based on the environment (sandbox or live).
if (APP_ENVIROMENT == 'sandbox') {
    $submitOrderUrl = "https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest";
} elseif (APP_ENVIROMENT == 'live') {
    $submitOrderUrl = "https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest";
} else {
    echo "Invalid APP_ENVIROMENT";
    exit;
}

// Set up the headers for the request. The 'Authorization' header requires the bearer token.
$headers = [
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
    'Authorization' => 'Bearer ' . $token // Include the bearer token for authentication
];

// Create the payload (data) that will be sent with the request to Pesapal.
$data = [
    'id' => $merchantreference,  
    'currency' => 'TZS',         
    'amount' => $amount,         
    'description' => 'subscription to ekiliSense premium',  
    'callback_url' => $callbackurl,
    'notification_id' => $ipn_id,   
    'branch' => $branch,            
    'billing_address' => [
        'email_address' => $email_address,
        'phone_number' => $phone,
        'country_code' => 'TZ',
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
        'line_1' => 'Pesapal Limited',
        'line_2' => '',
        'city' => '',
        'state' => '',
        'postal_code' => '',
        'zip_code' => ''
    ]
];

// Initialize the Guzzle client for HTTP requests
$client = new Client();

try {
    // Send the payment request to Pesapal
    $response = $client->post($submitOrderUrl, [
        'headers' => $headers,
        'json' => $data
    ]);

    $responseBody = $response->getBody();
    $responseCode = $response->getStatusCode();

    if ($responseCode == 200) {
        $responseData = json_decode($responseBody);

        if (isset($responseData->order_tracking_id)) {
            // Success: Extract the order tracking ID and redirect URL
            $redirectUrl = $responseData->redirect_url;

            // Display the payment iframe for the user to complete payment
            echo "<iframe src='$redirectUrl' width='100%' height='100%' frameborder='0' style='margin:auto'></iframe>";

            // Assume payment is successful. We need to get the consent token for future recurring payments
            $consentToken = $responseData->payment_method->token; 
            

            // Insert the subscription data into MySQL for future recurring payments
            $next_payment_date = date('Y-m-d', strtotime('+1 month'));  // Next payment date (for monthly subscription)

            $sql = "INSERT INTO subscriptions (user_id, amount, token, interval, last_payment, next_payment)
                    VALUES (1, $amount, '$consentToken', '$interval', CURDATE(), '$next_payment_date')";

            if (mysqli_query($conn, $sql)) {
                echo "Subscription record created successfully.";
            } else {
                echo "Error: " . mysqli_error($conn);
            }

        } else {
            // Handle case where order_tracking_id is missing
            echo "Error: Order tracking ID not found in the response.";
        }
    } else {
        // Handle non-200 HTTP response codes
        echo "Error: Failed to submit the order. HTTP response code: $responseCode";
    }
} catch (Exception $e) {
    // Handle exceptions like network issues or invalid requests
    echo "Error: " . $e->getMessage();
}

mysqli_close($conn);  // Close the database connection

// Handle IPN response after payment
function handleIPN($ipnData) {
    global $conn; // Use global connection variable
    // Here, $ipnData should be parsed from the incoming IPN request
    $school_subscription_id = $ipnData['school_subscription_id'];
    $payment_status = $ipnData['payment_status']; // e.g., 'successful'
    $transaction_id = $ipnData['transaction_id']; // Unique transaction ID

    // Insert IPN notification into the database
    $ipn_sql = "INSERT INTO IPN_Notifications (school_subscription_id, notification_type, notification_data)
                VALUES ('$school_subscription_id', 'payment', '" . json_encode($ipnData) . "')";

    if (mysqli_query($conn, $ipn_sql)) {
        // Update the payment status in the Payments table
        $payment_sql = "INSERT INTO Payments (school_subscription_id, amount, payment_status, transaction_id)
                        VALUES ('$school_subscription_id', {$ipnData['amount']}, '$payment_status', '$transaction_id')";
        mysqli_query($conn, $payment_sql);
    } else {
        echo "Error saving IPN notification: " . mysqli_error($conn);
    }
}

// Example of receiving an IPN (this would typically be called by an endpoint)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ipnData = json_decode(file_get_contents('php://input'), true); // Assuming IPN data is sent as JSON
    handleIPN($ipnData);
}

?>

<?php
/**
 * Initialization file
 * Loads all required classes and sets up the application
 */

// Load environment variables
require_once __DIR__ . '/../parseEnv.php';
parseEnv(__DIR__ . '/../.env');

// Start session with secure configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load helper classes
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Subscription.php';

// Configure secure session
Security::configureSecureSession();

// Database connection
$conn = new mysqli(
    "localhost", 
    getenv("DB_USERNAME"), 
    getenv("DB_PASSWORD"), 
    getenv("DB_NAME")
);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Initialize Database helper
$db = new Database($conn);

// Initialize Subscription manager
$subscriptionManager = new Subscription($db);

// Error reporting (adjust based on environment)
$environment = getenv('APP_ENVIRONMENT') ?: 'production';

if ($environment === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

/**
 * Helper function to get current user school UID
 * @return string|null
 */
function getCurrentSchoolUid() {
    return $_SESSION['School_uid'] ?? null;
}

/**
 * Helper function to check if user is authenticated
 * @return bool
 */
function isAuthenticated() {
    return isset($_SESSION['School_uid']);
}

/**
 * Helper function to redirect with message
 * @param string $url Redirect URL
 * @param string $message Optional message
 */
function redirect($url, $message = '') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: $url");
    exit;
}

/**
 * Helper function to get and clear flash message
 * @return string|null
 */
function getFlashMessage() {
    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    return $message;
}

/**
 * Helper function to JSON response
 * @param mixed $data Data to return
 * @param int $statusCode HTTP status code
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Helper function to validate required fields
 * @param array $fields Fields to check in $_POST
 * @return bool True if all fields present and not empty
 */
function validateRequiredFields($fields) {
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            return false;
        }
    }
    return true;
}

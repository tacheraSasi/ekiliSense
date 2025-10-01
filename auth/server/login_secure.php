<?php
/**
 * Secure Login Handler
 * Implements modern security practices for authentication
 */

require_once "../../includes/init.php";

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
}

// Validate required fields
if (!validateRequiredFields(['email', 'password'])) {
    jsonResponse(['success' => false, 'message' => 'All fields are required']);
}

// Sanitize inputs
$email = Security::sanitizeInput($_POST['email']);
$password = $_POST['password']; // Don't sanitize password, just use as-is

// Validate email format
if (!Security::validateEmail($email)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email format']);
}

// Check rate limiting
$rateLimit = Security::checkRateLimit($email, 5, 900); // 5 attempts per 15 minutes

if (!$rateLimit['allowed']) {
    $resetTime = date('H:i:s', $rateLimit['reset_time']);
    jsonResponse([
        'success' => false, 
        'message' => "Too many login attempts. Please try again after {$resetTime}",
        'retry_after' => $rateLimit['reset_time']
    ], 429);
}

try {
    // Query school by email using prepared statement
    $query = "SELECT * FROM schools WHERE School_email = ?";
    $school = $db->selectOne($query, [$email], 's');
    
    if (!$school) {
        // Increment rate limit counter
        Security::incrementRateLimit($email);
        
        jsonResponse([
            'success' => false, 
            'message' => 'Invalid email or password',
            'attempts_remaining' => $rateLimit['remaining'] - 1
        ]);
    }
    
    // Verify password
    $passwordValid = false;
    
    // Check if password is stored with modern hashing
    if (password_get_info($school['auth'])['algo'] !== null) {
        // Modern hash
        $passwordValid = Security::verifyPassword($password, $school['auth']);
    } else {
        // Legacy MD5 hash - verify and upgrade
        if (md5($password) === $school['auth']) {
            $passwordValid = true;
            
            // Upgrade to modern hash
            $newHash = Security::hashPassword($password);
            $updateQuery = "UPDATE schools SET auth = ? WHERE unique_id = ?";
            $db->execute($updateQuery, [$newHash, $school['unique_id']], 'ss');
        }
    }
    
    if (!$passwordValid) {
        // Increment rate limit counter
        Security::incrementRateLimit($email);
        
        jsonResponse([
            'success' => false, 
            'message' => 'Invalid email or password',
            'attempts_remaining' => $rateLimit['remaining'] - 1
        ]);
    }
    
    // Check subscription status
    $subscription = $subscriptionManager->getSubscription($school['unique_id']);
    $isActive = $subscriptionManager->isActive($school['unique_id']);
    
    if (!$subscription && !$isActive) {
        // No active subscription, check if trial is available
        $daysRemaining = $subscriptionManager->getDaysRemaining($school['unique_id']);
        
        if ($daysRemaining <= 0) {
            jsonResponse([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue.',
                'subscription_expired' => true
            ]);
        }
    }
    
    // Success! Reset rate limit and create session
    Security::resetRateLimit($email);
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['School_uid'] = $school['unique_id'];
    $_SESSION['School_email'] = $school['School_email'];
    $_SESSION['School_name'] = $school['School_name'];
    $_SESSION['authenticated_at'] = time();
    $_SESSION['last_activity'] = time();
    
    // Log successful login
    $logQuery = "INSERT INTO login_logs (school_uid, email, ip_address, user_agent, status, created_at) 
                 VALUES (?, ?, ?, ?, 'success', NOW())";
    $db->execute($logQuery, [
        $school['unique_id'],
        $email,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ], 'ssss');
    
    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => '/console/',
        'school' => [
            'name' => $school['School_name'],
            'uid' => $school['unique_id']
        ],
        'subscription' => [
            'active' => $isActive,
            'days_remaining' => $subscriptionManager->getDaysRemaining($school['unique_id'])
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ], 500);
}

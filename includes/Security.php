<?php

/**
 * Security Helper Class
 * Provides secure password hashing, CSRF protection, input validation, and rate limiting
 */
class Security
{

    /**
     * Hash password using modern secure algorithm
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password against hash
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool True if password matches
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate CSRF token
     * @return string CSRF token
     */
    public static function generateCSRFToken()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     * @param string $token Token to verify
     * @return bool True if token is valid
     */
    public static function verifyCSRFToken($token)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize input to prevent XSS
     * @param string $data Input data
     * @return string Sanitized data
     */
    public static function sanitizeInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /**
     * Validate email address
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check rate limiting for login attempts
     * @param string $identifier User identifier (email, IP)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 900)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $key = 'rate_limit_' . md5($identifier);
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'reset_time' => $now + $timeWindow
            ];
        }

        $data = $_SESSION[$key];

        // Reset if time window has passed
        if ($now > $data['reset_time']) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'reset_time' => $now + $timeWindow
            ];
            $data = $_SESSION[$key];
        }

        $remaining = max(0, $maxAttempts - $data['attempts']);
        $allowed = $data['attempts'] < $maxAttempts;

        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset_time' => $data['reset_time']
        ];
    }

    /**
     * Increment rate limit counter
     * @param string $identifier User identifier
     */
    public static function incrementRateLimit($identifier)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $key = 'rate_limit_' . md5($identifier);

        if (isset($_SESSION[$key])) {
            $_SESSION[$key]['attempts']++;
        }
    }

    /**
     * Reset rate limit for identifier
     * @param string $identifier User identifier
     */
    public static function resetRateLimit($identifier)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $key = 'rate_limit_' . md5($identifier);
        unset($_SESSION[$key]);
    }

    /**
     * Configure secure session settings
     */
    public static function configureSecureSession()
    {
        // Only set session parameters if no session is active
        if (session_status() == PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
        }

        // Regenerate session ID periodically
        if (session_status() == PHP_SESSION_ACTIVE) {
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }

    /**
     * Generate secure random string
     * @param int $length Length of string
     * @return string Random string
     */
    public static function generateRandomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Validate and sanitize phone number
     * @param string $phone Phone number
     * @return string|false Sanitized phone or false if invalid
     */
    public static function sanitizePhone($phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (strlen($phone) >= 10 && strlen($phone) <= 15) {
            return $phone;
        }
        return false;
    }
}

<?php
/**
 * Rate Limiting Middleware
 * Prevents API abuse by limiting requests per user/IP
 */

require_once __DIR__ . '/../utils/Response.php';

class RateLimitMiddleware {
    
    private static $maxRequests = 100; // Max requests
    private static $timeWindow = 60; // Time window in seconds (1 minute)
    
    /**
     * Check rate limit for current user/IP
     */
    public static function check() {
        // Get identifier (user ID or IP address)
        $identifier = self::getIdentifier();
        
        // Use simple file-based storage (can be upgraded to Redis/Memcached)
        $storageFile = sys_get_temp_dir() . '/rate_limit_' . md5($identifier) . '.json';
        
        $now = time();
        $data = [];
        
        // Load existing data
        if (file_exists($storageFile)) {
            $data = json_decode(file_get_contents($storageFile), true) ?: [];
        }
        
        // Remove old requests outside time window
        $data = array_filter($data, function($timestamp) use ($now) {
            return ($now - $timestamp) < self::$timeWindow;
        });
        
        // Check if limit exceeded
        if (count($data) >= self::$maxRequests) {
            $oldestRequest = min($data);
            $resetTime = $oldestRequest + self::$timeWindow;
            $waitTime = $resetTime - $now;
            
            Response::error(
                'Rate limit exceeded. Please try again later.',
                429,
                ['retry_after' => $waitTime]
            );
        }
        
        // Add current request
        $data[] = $now;
        
        // Save updated data
        file_put_contents($storageFile, json_encode($data));
        
        // Add rate limit headers
        header('X-RateLimit-Limit: ' . self::$maxRequests);
        header('X-RateLimit-Remaining: ' . (self::$maxRequests - count($data)));
        header('X-RateLimit-Reset: ' . ($now + self::$timeWindow));
    }
    
    /**
     * Get unique identifier for rate limiting
     */
    private static function getIdentifier() {
        // Try to get user ID from auth middleware
        if (isset($_SESSION['api_user']['user_id'])) {
            return 'user_' . $_SESSION['api_user']['user_id'];
        }
        
        // Fall back to IP address
        return 'ip_' . self::getClientIP();
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}

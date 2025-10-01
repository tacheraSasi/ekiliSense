<?php
/**
 * Authentication Middleware
 * Verifies JWT tokens for protected API endpoints
 */

require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware {
    
    public static $currentUser = null;
    
    /**
     * Verify JWT token from Authorization header
     */
    public static function verify() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
            Response::error('Authorization header missing', 401);
        }
        
        $authHeader = $headers['Authorization'] ?? $headers['authorization'];
        
        // Extract token from "Bearer {token}" format
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            Response::error('Invalid authorization format', 401);
        }
        
        $token = $matches[1];
        
        try {
            $payload = JWT::decode($token);
            
            // Validate token type
            if (!isset($payload['type']) || $payload['type'] !== 'access') {
                Response::error('Invalid token type', 401);
            }
            
            // Store user info for use in controllers
            self::$currentUser = $payload;
            
            // Make user data accessible globally
            $_SESSION['api_user'] = $payload;
            
        } catch (Exception $e) {
            Response::error('Invalid or expired token: ' . $e->getMessage(), 401);
        }
    }
    
    /**
     * Get current authenticated user
     */
    public static function getCurrentUser() {
        return self::$currentUser;
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        if (self::$currentUser === null) {
            return false;
        }
        
        $userRole = self::$currentUser['role'] ?? '';
        return $userRole === $role;
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($role) {
        if (!self::hasRole($role)) {
            Response::error('Insufficient permissions', 403);
        }
    }
    
    /**
     * Get school UID from token
     */
    public static function getSchoolUid() {
        return self::$currentUser['school_uid'] ?? null;
    }
    
    /**
     * Get user ID from token
     */
    public static function getUserId() {
        return self::$currentUser['user_id'] ?? null;
    }
}

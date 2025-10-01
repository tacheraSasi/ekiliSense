<?php
/**
 * JWT (JSON Web Token) Handler
 * Simple JWT implementation for API authentication
 */

class JWT {
    
    private static $secret_key = null;
    private static $algorithm = 'HS256';
    
    /**
     * Get or generate secret key
     */
    private static function getSecretKey() {
        if (self::$secret_key === null) {
            // Use environment variable or generate one
            self::$secret_key = getenv('JWT_SECRET') ?: 'ekiliSense_secret_key_change_in_production';
        }
        return self::$secret_key;
    }
    
    /**
     * Encode JWT token
     */
    public static function encode($payload, $expiry = 3600) {
        $header = [
            'typ' => 'JWT',
            'alg' => self::$algorithm
        ];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        
        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            self::getSecretKey(),
            true
        );
        
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    /**
     * Decode JWT token
     */
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        // Verify signature
        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            self::getSecretKey(),
            true
        );
        
        $expectedSignature = self::base64UrlEncode($signature);
        
        if ($base64UrlSignature !== $expectedSignature) {
            throw new Exception('Invalid token signature');
        }
        
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Create access token
     */
    public static function createAccessToken($userId, $schoolUid, $role, $email) {
        return self::encode([
            'user_id' => $userId,
            'school_uid' => $schoolUid,
            'role' => $role,
            'email' => $email,
            'type' => 'access'
        ], 3600); // 1 hour
    }
    
    /**
     * Create refresh token
     */
    public static function createRefreshToken($userId, $schoolUid) {
        return self::encode([
            'user_id' => $userId,
            'school_uid' => $schoolUid,
            'type' => 'refresh'
        ], 604800); // 7 days
    }
}

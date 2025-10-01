<?php
/**
 * Standardized API Response Handler
 */

class Response {
    
    /**
     * Send success response
     */
    public static function success($data = [], $message = 'Success', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
    
    /**
     * Send error response
     */
    public static function error($message = 'Error', $code = 400, $errors = []) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
    
    /**
     * Send paginated response
     */
    public static function paginated($data, $total, $page, $perPage, $message = 'Success') {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $required) {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "The {$field} field is required";
            }
        }
        
        if (!empty($errors)) {
            self::error('Validation failed', 422, $errors);
        }
        
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

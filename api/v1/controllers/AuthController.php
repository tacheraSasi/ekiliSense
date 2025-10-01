<?php
/**
 * Authentication Controller
 * Handles login, registration, token refresh
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWT.php';

class AuthController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Login endpoint
     * POST /api/v1/auth/login
     */
    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['email', 'password']);
        
        $email = Response::sanitize($input['email']);
        $password = $input['password'];
        
        // Check if it's a school admin login
        $stmt = $this->conn->prepare("SELECT * FROM schools WHERE school_email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $school = $result->fetch_assoc();
            
            // Verify password (supports both old MD5 and new bcrypt)
            $passwordValid = false;
            
            if (password_verify($password, $school['school_password'])) {
                $passwordValid = true;
            } elseif (md5($password) === $school['school_password']) {
                // Old MD5 password - upgrade to bcrypt
                $passwordValid = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $this->conn->prepare("UPDATE schools SET school_password = ? WHERE unique_id = ?");
                $updateStmt->bind_param("ss", $newHash, $school['unique_id']);
                $updateStmt->execute();
            }
            
            if (!$passwordValid) {
                Response::error('Invalid credentials', 401);
            }
            
            // Generate tokens
            $accessToken = JWT::createAccessToken(
                $school['unique_id'],
                $school['unique_id'],
                'admin',
                $school['school_email']
            );
            
            $refreshToken = JWT::createRefreshToken(
                $school['unique_id'],
                $school['unique_id']
            );
            
            Response::success([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user' => [
                    'id' => $school['unique_id'],
                    'name' => $school['School_name'],
                    'email' => $school['school_email'],
                    'role' => 'admin'
                ]
            ], 'Login successful');
        }
        
        // Check if it's a teacher login
        $stmt = $this->conn->prepare("SELECT * FROM teachers WHERE teacher_email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $teacher = $result->fetch_assoc();
            
            // Verify password
            $passwordValid = false;
            
            if (password_verify($password, $teacher['teacher_password'])) {
                $passwordValid = true;
            } elseif (md5($password) === $teacher['teacher_password']) {
                // Old MD5 password - upgrade to bcrypt
                $passwordValid = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $this->conn->prepare("UPDATE teachers SET teacher_password = ? WHERE teacher_id = ?");
                $updateStmt->bind_param("ss", $newHash, $teacher['teacher_id']);
                $updateStmt->execute();
            }
            
            if (!$passwordValid) {
                Response::error('Invalid credentials', 401);
            }
            
            // Generate tokens
            $accessToken = JWT::createAccessToken(
                $teacher['teacher_id'],
                $teacher['School_unique_id'],
                'teacher',
                $teacher['teacher_email']
            );
            
            $refreshToken = JWT::createRefreshToken(
                $teacher['teacher_id'],
                $teacher['School_unique_id']
            );
            
            Response::success([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'user' => [
                    'id' => $teacher['teacher_id'],
                    'name' => $teacher['teacher_fullname'],
                    'email' => $teacher['teacher_email'],
                    'role' => 'teacher',
                    'school_uid' => $teacher['School_unique_id']
                ]
            ], 'Login successful');
        }
        
        Response::error('Invalid credentials', 401);
    }
    
    /**
     * Register new school (for demo purposes - can be restricted)
     * POST /api/v1/auth/register
     */
    public function register() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['school_name', 'email', 'password', 'phone']);
        
        $schoolName = Response::sanitize($input['school_name']);
        $email = Response::sanitize($input['email']);
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        $phone = Response::sanitize($input['phone']);
        $uniqueId = uniqid('school_', true);
        
        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT unique_id FROM schools WHERE school_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            Response::error('Email already registered', 409);
        }
        
        // Insert new school
        $stmt = $this->conn->prepare(
            "INSERT INTO schools (unique_id, School_name, school_email, school_password, school_phone_no) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $uniqueId, $schoolName, $email, $password, $phone);
        
        if ($stmt->execute()) {
            Response::success([
                'school_id' => $uniqueId,
                'message' => 'School registered successfully. Please login to continue.'
            ], 'Registration successful', 201);
        } else {
            Response::error('Registration failed', 500);
        }
    }
    
    /**
     * Refresh access token
     * POST /api/v1/auth/refresh
     */
    public function refresh() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['refresh_token']);
        
        try {
            $payload = JWT::decode($input['refresh_token']);
            
            if ($payload['type'] !== 'refresh') {
                Response::error('Invalid token type', 401);
            }
            
            // Generate new access token
            $accessToken = JWT::createAccessToken(
                $payload['user_id'],
                $payload['school_uid'],
                $payload['role'] ?? 'user',
                $payload['email'] ?? ''
            );
            
            Response::success([
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ], 'Token refreshed successfully');
            
        } catch (Exception $e) {
            Response::error('Invalid refresh token', 401);
        }
    }
    
    /**
     * Logout (client-side token removal)
     * POST /api/v1/auth/logout
     */
    public function logout() {
        // In a JWT system, logout is typically handled client-side
        // But we can add token blacklisting here if needed
        Response::success([], 'Logged out successfully');
    }
}

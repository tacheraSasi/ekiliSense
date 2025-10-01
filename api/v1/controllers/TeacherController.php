<?php
/**
 * Teacher Controller
 * Manages teacher operations
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class TeacherController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * List all teachers
     * GET /api/v1/teachers
     */
    public function list() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT teacher_id, teacher_fullname, teacher_email, teacher_active_phone, created_at
            FROM teachers 
            WHERE School_unique_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $teachers = [];
        while ($row = $result->fetch_assoc()) {
            $teachers[] = [
                'id' => $row['teacher_id'],
                'name' => $row['teacher_fullname'],
                'email' => $row['teacher_email'],
                'phone' => $row['teacher_active_phone'],
                'created_at' => $row['created_at']
            ];
        }
        
        Response::success($teachers);
    }
    
    /**
     * Get single teacher
     * GET /api/v1/teachers/{id}
     */
    public function get($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT * FROM teachers 
            WHERE teacher_id = ? AND School_unique_id = ?
        ");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            Response::error('Teacher not found', 404);
        }
        
        $teacher = $result->fetch_assoc();
        
        Response::success([
            'id' => $teacher['teacher_id'],
            'name' => $teacher['teacher_fullname'],
            'email' => $teacher['teacher_email'],
            'phone' => $teacher['teacher_active_phone'],
            'address' => $teacher['teacher_home_address']
        ]);
    }
    
    /**
     * Create teacher
     * POST /api/v1/teachers
     */
    public function create() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['name', 'email']);
        
        $name = Response::sanitize($input['name']);
        $email = Response::sanitize($input['email']);
        $phone = isset($input['phone']) ? Response::sanitize($input['phone']) : '';
        $teacherId = uniqid('teacher_', true);
        
        $stmt = $this->conn->prepare("
            INSERT INTO teachers (teacher_id, teacher_fullname, teacher_email, teacher_active_phone, School_unique_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $teacherId, $name, $email, $phone, $schoolUid);
        
        if ($stmt->execute()) {
            Response::success(['teacher_id' => $teacherId], 'Teacher created successfully', 201);
        } else {
            Response::error('Failed to create teacher', 500);
        }
    }
    
    /**
     * Update teacher
     * PUT /api/v1/teachers/{id}
     */
    public function update($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        $updates = [];
        $params = [];
        $types = '';
        
        if (isset($input['name'])) {
            $updates[] = "teacher_fullname = ?";
            $params[] = Response::sanitize($input['name']);
            $types .= 's';
        }
        
        if (isset($input['phone'])) {
            $updates[] = "teacher_active_phone = ?";
            $params[] = Response::sanitize($input['phone']);
            $types .= 's';
        }
        
        if (empty($updates)) {
            Response::error('No fields to update', 400);
        }
        
        $params[] = $teacherId;
        $params[] = $schoolUid;
        $types .= 'ss';
        
        $sql = "UPDATE teachers SET " . implode(', ', $updates) . " WHERE teacher_id = ? AND School_unique_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            Response::success([], 'Teacher updated successfully');
        } else {
            Response::error('Failed to update teacher', 500);
        }
    }
}

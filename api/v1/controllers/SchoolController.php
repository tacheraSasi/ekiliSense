<?php
/**
 * School Controller
 * Manages school profile and settings
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class SchoolController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get school profile
     * GET /api/v1/schools/profile
     */
    public function getProfile() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("SELECT * FROM schools WHERE unique_id = ?");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            Response::error('School not found', 404);
        }
        
        $school = $result->fetch_assoc();
        
        Response::success([
            'id' => $school['unique_id'],
            'name' => $school['School_name'],
            'email' => $school['school_email'],
            'phone' => $school['school_phone_no'],
            'address' => $school['school_address'] ?? '',
            'created_at' => $school['created_at']
        ]);
    }
    
    /**
     * Update school profile
     * PUT /api/v1/schools/profile
     */
    public function updateProfile() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        $updates = [];
        $params = [];
        $types = '';
        
        if (isset($input['name'])) {
            $updates[] = "School_name = ?";
            $params[] = Response::sanitize($input['name']);
            $types .= 's';
        }
        
        if (isset($input['phone'])) {
            $updates[] = "school_phone_no = ?";
            $params[] = Response::sanitize($input['phone']);
            $types .= 's';
        }
        
        if (isset($input['address'])) {
            $updates[] = "school_address = ?";
            $params[] = Response::sanitize($input['address']);
            $types .= 's';
        }
        
        if (empty($updates)) {
            Response::error('No fields to update', 400);
        }
        
        $params[] = $schoolUid;
        $types .= 's';
        
        $sql = "UPDATE schools SET " . implode(', ', $updates) . " WHERE unique_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            Response::success([], 'Profile updated successfully');
        } else {
            Response::error('Failed to update profile', 500);
        }
    }
    
    /**
     * Get school statistics
     * GET /api/v1/schools/stats
     */
    public function getStats() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Get student count
        $studentsStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM students WHERE school_uid = ?");
        $studentsStmt->bind_param("s", $schoolUid);
        $studentsStmt->execute();
        $studentCount = $studentsStmt->get_result()->fetch_assoc()['count'];
        
        // Get teacher count
        $teachersStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM teachers WHERE School_unique_id = ?");
        $teachersStmt->bind_param("s", $schoolUid);
        $teachersStmt->execute();
        $teacherCount = $teachersStmt->get_result()->fetch_assoc()['count'];
        
        // Get class count
        $classesStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM classes WHERE school_unique_id = ?");
        $classesStmt->bind_param("s", $schoolUid);
        $classesStmt->execute();
        $classCount = $classesStmt->get_result()->fetch_assoc()['count'];
        
        Response::success([
            'students' => (int)$studentCount,
            'teachers' => (int)$teacherCount,
            'classes' => (int)$classCount
        ]);
    }
}

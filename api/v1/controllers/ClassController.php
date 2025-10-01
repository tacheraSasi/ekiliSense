<?php
/**
 * Class Controller
 * Manages class operations
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ClassController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * List all classes
     * GET /api/v1/classes
     */
    public function list() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT class_id, class_name, class_shortname, created_at
            FROM classes 
            WHERE school_unique_id = ?
            ORDER BY class_name ASC
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            // Get student count for each class
            $countStmt = $this->conn->prepare("SELECT COUNT(*) as count FROM students WHERE class_id = ?");
            $countStmt->bind_param("s", $row['class_id']);
            $countStmt->execute();
            $studentCount = $countStmt->get_result()->fetch_assoc()['count'];
            
            $classes[] = [
                'id' => $row['class_id'],
                'name' => $row['class_name'],
                'short_name' => $row['class_shortname'],
                'student_count' => (int)$studentCount,
                'created_at' => $row['created_at']
            ];
        }
        
        Response::success($classes);
    }
    
    /**
     * Get single class with details
     * GET /api/v1/classes/{id}
     */
    public function get($classId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT * FROM classes 
            WHERE class_id = ? AND school_unique_id = ?
        ");
        $stmt->bind_param("ss", $classId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            Response::error('Class not found', 404);
        }
        
        $class = $result->fetch_assoc();
        
        // Get students in this class
        $studentsStmt = $this->conn->prepare("
            SELECT student_id, student_fullname, student_email
            FROM students 
            WHERE class_id = ? AND school_uid = ?
        ");
        $studentsStmt->bind_param("ss", $classId, $schoolUid);
        $studentsStmt->execute();
        $studentsResult = $studentsStmt->get_result();
        
        $students = [];
        while ($student = $studentsResult->fetch_assoc()) {
            $students[] = [
                'id' => $student['student_id'],
                'name' => $student['student_fullname'],
                'email' => $student['student_email']
            ];
        }
        
        Response::success([
            'id' => $class['class_id'],
            'name' => $class['class_name'],
            'short_name' => $class['class_shortname'],
            'students' => $students
        ]);
    }
    
    /**
     * Create new class
     * POST /api/v1/classes
     */
    public function create() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['name', 'short_name']);
        
        $name = Response::sanitize($input['name']);
        $shortName = Response::sanitize($input['short_name']);
        $classId = uniqid('class_', true);
        
        $stmt = $this->conn->prepare("
            INSERT INTO classes (class_id, class_name, class_shortname, school_unique_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $classId, $name, $shortName, $schoolUid);
        
        if ($stmt->execute()) {
            Response::success(['class_id' => $classId], 'Class created successfully', 201);
        } else {
            Response::error('Failed to create class', 500);
        }
    }
}

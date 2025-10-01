<?php
/**
 * Student Controller
 * Manages student CRUD operations
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class StudentController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * List all students for school
     * GET /api/v1/students
     */
    public function list() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? min((int)$_GET['per_page'], 100) : 20;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countStmt = $this->conn->prepare("SELECT COUNT(*) as total FROM students WHERE school_uid = ?");
        $countStmt->bind_param("s", $schoolUid);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        
        // Get students with class info
        $stmt = $this->conn->prepare("
            SELECT 
                s.*,
                c.class_name,
                c.class_shortname
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.class_id AND s.school_uid = c.school_unique_id
            WHERE s.school_uid = ?
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("sii", $schoolUid, $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = [
                'id' => $row['student_id'],
                'name' => $row['student_fullname'],
                'email' => $row['student_email'],
                'phone' => $row['student_phone'],
                'class_id' => $row['class_id'],
                'class_name' => $row['class_name'],
                'enrollment_date' => $row['created_at']
            ];
        }
        
        Response::paginated($students, $total, $page, $perPage);
    }
    
    /**
     * Get single student
     * GET /api/v1/students/{id}
     */
    public function get($studentId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT 
                s.*,
                c.class_name,
                c.class_shortname
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.class_id
            WHERE s.student_id = ? AND s.school_uid = ?
        ");
        $stmt->bind_param("ss", $studentId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            Response::error('Student not found', 404);
        }
        
        $student = $result->fetch_assoc();
        
        Response::success([
            'id' => $student['student_id'],
            'name' => $student['student_fullname'],
            'email' => $student['student_email'],
            'phone' => $student['student_phone'],
            'class_id' => $student['class_id'],
            'class_name' => $student['class_name'],
            'enrollment_date' => $student['created_at']
        ]);
    }
    
    /**
     * Create new student
     * POST /api/v1/students
     */
    public function create() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['name', 'email', 'class_id']);
        
        $name = Response::sanitize($input['name']);
        $email = Response::sanitize($input['email']);
        $classId = Response::sanitize($input['class_id']);
        $phone = isset($input['phone']) ? Response::sanitize($input['phone']) : '';
        $studentId = uniqid('student_', true);
        
        // Check if email already exists
        $checkStmt = $this->conn->prepare("SELECT student_id FROM students WHERE student_email = ? AND school_uid = ?");
        $checkStmt->bind_param("ss", $email, $schoolUid);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            Response::error('Email already exists', 409);
        }
        
        // Insert student
        $stmt = $this->conn->prepare("
            INSERT INTO students (student_id, student_fullname, student_email, student_phone, class_id, school_uid)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss", $studentId, $name, $email, $phone, $classId, $schoolUid);
        
        if ($stmt->execute()) {
            Response::success([
                'student_id' => $studentId,
                'name' => $name,
                'email' => $email
            ], 'Student created successfully', 201);
        } else {
            Response::error('Failed to create student', 500);
        }
    }
    
    /**
     * Update student
     * PUT /api/v1/students/{id}
     */
    public function update($studentId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verify student belongs to school
        $checkStmt = $this->conn->prepare("SELECT student_id FROM students WHERE student_id = ? AND school_uid = ?");
        $checkStmt->bind_param("ss", $studentId, $schoolUid);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows === 0) {
            Response::error('Student not found', 404);
        }
        
        $updates = [];
        $params = [];
        $types = '';
        
        if (isset($input['name'])) {
            $updates[] = "student_fullname = ?";
            $params[] = Response::sanitize($input['name']);
            $types .= 's';
        }
        
        if (isset($input['email'])) {
            $updates[] = "student_email = ?";
            $params[] = Response::sanitize($input['email']);
            $types .= 's';
        }
        
        if (isset($input['phone'])) {
            $updates[] = "student_phone = ?";
            $params[] = Response::sanitize($input['phone']);
            $types .= 's';
        }
        
        if (isset($input['class_id'])) {
            $updates[] = "class_id = ?";
            $params[] = Response::sanitize($input['class_id']);
            $types .= 's';
        }
        
        if (empty($updates)) {
            Response::error('No fields to update', 400);
        }
        
        $params[] = $studentId;
        $params[] = $schoolUid;
        $types .= 'ss';
        
        $sql = "UPDATE students SET " . implode(', ', $updates) . " WHERE student_id = ? AND school_uid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            Response::success([], 'Student updated successfully');
        } else {
            Response::error('Failed to update student', 500);
        }
    }
    
    /**
     * Delete student
     * DELETE /api/v1/students/{id}
     */
    public function delete($studentId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("DELETE FROM students WHERE student_id = ? AND school_uid = ?");
        $stmt->bind_param("ss", $studentId, $schoolUid);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            Response::success([], 'Student deleted successfully');
        } else {
            Response::error('Student not found or already deleted', 404);
        }
    }
}

<?php
/**
 * Assignment Controller
 * Manages homework assignments
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AssignmentController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * List assignments
     * GET /api/v1/assignments
     */
    public function list() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT 
                ha.*,
                c.class_name,
                t.teacher_fullname
            FROM homework_assignments ha
            LEFT JOIN classes c ON ha.class_id = c.class_id
            LEFT JOIN teachers t ON ha.teacher_id = t.teacher_id
            WHERE ha.school_uid = ?
            ORDER BY ha.due_date DESC
            LIMIT 50
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = [
                'id' => $row['assignment_uid'],
                'title' => $row['title'],
                'description' => $row['description'],
                'class' => $row['class_name'],
                'teacher' => $row['teacher_fullname'],
                'due_date' => $row['due_date'],
                'status' => $row['status'],
                'max_points' => $row['max_points']
            ];
        }
        
        Response::success($assignments);
    }
    
    /**
     * Get single assignment
     * GET /api/v1/assignments/{id}
     */
    public function get($assignmentId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT * FROM homework_assignments 
            WHERE assignment_uid = ? AND school_uid = ?
        ");
        $stmt->bind_param("ss", $assignmentId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            Response::error('Assignment not found', 404);
        }
        
        $assignment = $result->fetch_assoc();
        Response::success($assignment);
    }
    
    /**
     * Create assignment
     * POST /api/v1/assignments
     */
    public function create() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $userId = AuthMiddleware::getUserId();
        $input = json_decode(file_get_contents('php://input'), true);
        
        Response::validateRequired($input, ['title', 'class_id', 'subject_id', 'due_date']);
        
        $assignmentUid = uniqid('assignment_', true);
        $title = Response::sanitize($input['title']);
        $description = isset($input['description']) ? Response::sanitize($input['description']) : '';
        $classId = Response::sanitize($input['class_id']);
        $subjectId = Response::sanitize($input['subject_id']);
        $dueDate = Response::sanitize($input['due_date']);
        $maxPoints = isset($input['max_points']) ? (int)$input['max_points'] : 100;
        
        $stmt = $this->conn->prepare("
            INSERT INTO homework_assignments 
            (assignment_uid, school_uid, class_id, subject_id, teacher_id, title, description, due_date, max_points)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssssi", $assignmentUid, $schoolUid, $classId, $subjectId, $userId, $title, $description, $dueDate, $maxPoints);
        
        if ($stmt->execute()) {
            Response::success(['assignment_id' => $assignmentUid], 'Assignment created successfully', 201);
        } else {
            Response::error('Failed to create assignment', 500);
        }
    }
}

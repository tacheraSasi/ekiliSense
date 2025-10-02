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
    
    /**
     * Get teacher's classes
     * GET /api/v1/teachers/{id}/classes
     */
    public function getClasses($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Verify teacher belongs to school
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND School_unique_id = ?");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            Response::error('Teacher not found', 404);
        }
        
        // Get classes taught by this teacher
        $stmt = $this->conn->prepare("
            SELECT DISTINCT c.Class_id, c.Class_name, c.Class_short_name,
                   COUNT(DISTINCT s.subject_id) as subject_count,
                   COUNT(DISTINCT st.student_id) as student_count
            FROM classes c
            JOIN subjects s ON c.Class_id = s.class_id
            LEFT JOIN students st ON c.Class_id = st.class_id
            WHERE s.teacher_id = ? AND c.school_unique_id = ?
            GROUP BY c.Class_id
            ORDER BY c.Class_name
        ");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = [
                'id' => $row['Class_id'],
                'name' => $row['Class_name'],
                'short_name' => $row['Class_short_name'],
                'subject_count' => (int)$row['subject_count'],
                'student_count' => (int)$row['student_count']
            ];
        }
        
        Response::success($classes);
    }
    
    /**
     * Get teacher's students
     * GET /api/v1/teachers/{id}/students
     */
    public function getStudents($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Verify teacher belongs to school
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND School_unique_id = ?");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            Response::error('Teacher not found', 404);
        }
        
        // Get students taught by this teacher
        $stmt = $this->conn->prepare("
            SELECT DISTINCT st.student_id, st.student_first_name, st.student_last_name, 
                   st.student_email, c.Class_name
            FROM students st
            JOIN subjects s ON st.class_id = s.class_id
            JOIN classes c ON st.class_id = c.Class_id
            WHERE s.teacher_id = ?
            ORDER BY st.student_first_name, st.student_last_name
        ");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = [
                'id' => $row['student_id'],
                'first_name' => $row['student_first_name'],
                'last_name' => $row['student_last_name'],
                'email' => $row['student_email'],
                'class' => $row['Class_name']
            ];
        }
        
        Response::success($students);
    }
    
    /**
     * Get teacher's assignments
     * GET /api/v1/teachers/{id}/assignments
     */
    public function getAssignments($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Verify teacher belongs to school
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND School_unique_id = ?");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            Response::error('Teacher not found', 404);
        }
        
        // Get assignments
        $stmt = $this->conn->prepare("
            SELECT ha.assignment_uid, ha.assignment_title, ha.assignment_description,
                   ha.deadline, ha.created_at, s.subject_name, c.Class_name,
                   COUNT(hs.submission_id) as submission_count
            FROM homework_assignments ha
            JOIN subjects s ON ha.subject_id = s.subject_id
            JOIN classes c ON s.class_id = c.Class_id
            LEFT JOIN homework_submissions hs ON ha.assignment_uid = hs.assignment_uid
            WHERE ha.teacher_id = ?
            GROUP BY ha.assignment_uid
            ORDER BY ha.deadline DESC
        ");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = [
                'id' => $row['assignment_uid'],
                'title' => $row['assignment_title'],
                'description' => $row['assignment_description'],
                'subject' => $row['subject_name'],
                'class' => $row['Class_name'],
                'deadline' => $row['deadline'],
                'created_at' => $row['created_at'],
                'submission_count' => (int)$row['submission_count']
            ];
        }
        
        Response::success($assignments);
    }
    
    /**
     * Create assignment for teacher
     * POST /api/v1/teachers/{id}/assignments
     */
    public function createAssignment($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verify teacher belongs to school
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND School_unique_id = ?");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            Response::error('Teacher not found', 404);
        }
        
        Response::validateRequired($input, ['title', 'subject_id', 'deadline']);
        
        $title = Response::sanitize($input['title']);
        $subjectId = Response::sanitize($input['subject_id']);
        $description = isset($input['description']) ? Response::sanitize($input['description']) : '';
        $deadline = Response::sanitize($input['deadline']);
        
        // Verify teacher teaches this subject
        $stmt = $this->conn->prepare("SELECT subject_id FROM subjects WHERE subject_id = ? AND teacher_id = ?");
        $stmt->bind_param("ss", $subjectId, $teacherId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            Response::error('You do not have permission to create assignments for this subject', 403);
        }
        
        $assignmentUid = uniqid('assignment_', true);
        
        $stmt = $this->conn->prepare("
            INSERT INTO homework_assignments 
            (assignment_uid, assignment_title, assignment_description, subject_id, teacher_id, deadline, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("ssssss", $assignmentUid, $title, $description, $subjectId, $teacherId, $deadline);
        
        if ($stmt->execute()) {
            Response::success(['assignment_id' => $assignmentUid], 'Assignment created successfully', 201);
        } else {
            Response::error('Failed to create assignment', 500);
        }
    }
    
    /**
     * Get teacher's subjects
     * GET /api/v1/teachers/{id}/subjects
     */
    public function getSubjects($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Verify teacher belongs to school
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND School_unique_id = ?");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            Response::error('Teacher not found', 404);
        }
        
        // Get subjects
        $stmt = $this->conn->prepare("
            SELECT s.subject_id, s.subject_name, c.Class_name, c.Class_id,
                   COUNT(DISTINCT st.student_id) as student_count
            FROM subjects s
            JOIN classes c ON s.class_id = c.Class_id
            LEFT JOIN students st ON c.Class_id = st.class_id
            WHERE s.teacher_id = ?
            GROUP BY s.subject_id
            ORDER BY c.Class_name, s.subject_name
        ");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = [
                'id' => $row['subject_id'],
                'name' => $row['subject_name'],
                'class_id' => $row['Class_id'],
                'class_name' => $row['Class_name'],
                'student_count' => (int)$row['student_count']
            ];
        }
        
        Response::success($subjects);
    }
    
    /**
     * Get teacher performance stats
     * GET /api/v1/teachers/{id}/performance
     */
    public function getPerformance($teacherId) {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Verify teacher belongs to school
        $stmt = $this->conn->prepare("SELECT teacher_id FROM teachers WHERE teacher_id = ? AND School_unique_id = ?");
        $stmt->bind_param("ss", $teacherId, $schoolUid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            Response::error('Teacher not found', 404);
        }
        
        // Get total assignments
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM homework_assignments WHERE teacher_id = ?");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $totalAssignments = $stmt->get_result()->fetch_assoc()['count'];
        
        // Get pending assignments
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM homework_assignments WHERE teacher_id = ? AND deadline >= CURDATE()");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $pendingAssignments = $stmt->get_result()->fetch_assoc()['count'];
        
        // Get total classes
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT c.Class_id) as count
            FROM classes c
            JOIN subjects s ON c.Class_id = s.class_id
            WHERE s.teacher_id = ?
        ");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $totalClasses = $stmt->get_result()->fetch_assoc()['count'];
        
        // Get total students
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT st.student_id) as count
            FROM students st
            JOIN subjects s ON st.class_id = s.class_id
            WHERE s.teacher_id = ?
        ");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $totalStudents = $stmt->get_result()->fetch_assoc()['count'];
        
        // Get total subjects
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM subjects WHERE teacher_id = ?");
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $totalSubjects = $stmt->get_result()->fetch_assoc()['count'];
        
        Response::success([
            'total_assignments' => (int)$totalAssignments,
            'pending_assignments' => (int)$pendingAssignments,
            'total_classes' => (int)$totalClasses,
            'total_students' => (int)$totalStudents,
            'total_subjects' => (int)$totalSubjects
        ]);
    }
}

<?php
/**
 * Parent Controller
 * Handles parent portal features - NEW FEATURE for SaaS
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ParentController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get parent's children
     * GET /api/v1/parent/children
     */
    public function getChildren() {
        $userId = AuthMiddleware::getUserId();
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // For now, we'll assume parent_id is linked to student records
        // This would need a proper parent-student relationship table in production
        $stmt = $this->conn->prepare("
            SELECT 
                s.student_id,
                s.student_fullname,
                s.student_email,
                c.class_name,
                c.class_shortname
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.class_id
            WHERE s.parent_id = ? AND s.school_uid = ?
        ");
        $stmt->bind_param("ss", $userId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $children = [];
        while ($row = $result->fetch_assoc()) {
            $children[] = [
                'id' => $row['student_id'],
                'name' => $row['student_fullname'],
                'email' => $row['student_email'],
                'class' => $row['class_name']
            ];
        }
        
        Response::success($children);
    }
    
    /**
     * Get child's grades
     * GET /api/v1/parent/children/{id}/grades
     */
    public function getGrades($studentId) {
        $userId = AuthMiddleware::getUserId();
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Verify this student belongs to the parent
        $verifyStmt = $this->conn->prepare("
            SELECT student_id FROM students 
            WHERE student_id = ? AND parent_id = ? AND school_uid = ?
        ");
        $verifyStmt->bind_param("sss", $studentId, $userId, $schoolUid);
        $verifyStmt->execute();
        
        if ($verifyStmt->get_result()->num_rows === 0) {
            Response::error('Access denied', 403);
        }
        
        // Get exam results
        $stmt = $this->conn->prepare("
            SELECT 
                er.marks_obtained,
                er.grade,
                er.result_date,
                es.exam_title,
                es.max_marks,
                es.exam_date
            FROM exam_results er
            JOIN exam_schedules es ON er.exam_uid = es.exam_uid
            WHERE er.student_id = ? AND er.school_uid = ?
            ORDER BY es.exam_date DESC
        ");
        $stmt->bind_param("ss", $studentId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[] = [
                'exam' => $row['exam_title'],
                'marks' => $row['marks_obtained'],
                'max_marks' => $row['max_marks'],
                'grade' => $row['grade'],
                'percentage' => round(($row['marks_obtained'] / $row['max_marks']) * 100, 2),
                'date' => $row['exam_date']
            ];
        }
        
        Response::success($grades);
    }
    
    /**
     * Get child's attendance
     * GET /api/v1/parent/children/{id}/attendance
     */
    public function getAttendance($studentId) {
        $userId = AuthMiddleware::getUserId();
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Verify this student belongs to the parent
        $verifyStmt = $this->conn->prepare("
            SELECT student_id FROM students 
            WHERE student_id = ? AND parent_id = ? AND school_uid = ?
        ");
        $verifyStmt->bind_param("sss", $studentId, $userId, $schoolUid);
        $verifyStmt->execute();
        
        if ($verifyStmt->get_result()->num_rows === 0) {
            Response::error('Access denied', 403);
        }
        
        // Get attendance records
        $stmt = $this->conn->prepare("
            SELECT 
                attendance_date,
                status,
                remarks
            FROM student_attendance
            WHERE student_id = ? AND school_uid = ?
            ORDER BY attendance_date DESC
            LIMIT 30
        ");
        $stmt->bind_param("ss", $studentId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attendance = [];
        $totalDays = 0;
        $presentDays = 0;
        
        while ($row = $result->fetch_assoc()) {
            $attendance[] = [
                'date' => $row['attendance_date'],
                'status' => $row['status'],
                'remarks' => $row['remarks']
            ];
            
            $totalDays++;
            if ($row['status'] === 'present') {
                $presentDays++;
            }
        }
        
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
        
        Response::success([
            'records' => $attendance,
            'summary' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $totalDays - $presentDays,
                'attendance_rate' => $attendanceRate
            ]
        ]);
    }
    
    /**
     * Get notifications for parent
     * GET /api/v1/parent/notifications
     */
    public function getNotifications() {
        $userId = AuthMiddleware::getUserId();
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Get notifications (announcements, homework, etc.)
        $stmt = $this->conn->prepare("
            SELECT 
                notification_id,
                title,
                message,
                type,
                created_at,
                is_read
            FROM notifications
            WHERE user_id = ? AND school_uid = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->bind_param("ss", $userId, $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['notification_id'],
                'title' => $row['title'],
                'message' => $row['message'],
                'type' => $row['type'],
                'created_at' => $row['created_at'],
                'is_read' => (bool)$row['is_read']
            ];
        }
        
        Response::success($notifications);
    }
}

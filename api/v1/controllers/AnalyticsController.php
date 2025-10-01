<?php
/**
 * Analytics Controller
 * Provides business intelligence and reporting - NEW SAAS FEATURE
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AnalyticsController {
    
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get school dashboard analytics
     * GET /api/v1/analytics/dashboard
     */
    public function getDashboard() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        // Student statistics
        $studentStats = $this->getStudentStats($schoolUid);
        
        // Teacher statistics
        $teacherStats = $this->getTeacherStats($schoolUid);
        
        // Assignment statistics
        $assignmentStats = $this->getAssignmentStats($schoolUid);
        
        // Attendance statistics
        $attendanceStats = $this->getAttendanceStats($schoolUid);
        
        // Performance trends
        $performanceTrends = $this->getPerformanceTrends($schoolUid);
        
        Response::success([
            'students' => $studentStats,
            'teachers' => $teacherStats,
            'assignments' => $assignmentStats,
            'attendance' => $attendanceStats,
            'performance_trends' => $performanceTrends
        ]);
    }
    
    /**
     * Get student performance report
     * GET /api/v1/analytics/student-performance
     */
    public function getStudentPerformance() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        $classId = isset($_GET['class_id']) ? Response::sanitize($_GET['class_id']) : null;
        
        $query = "
            SELECT 
                s.student_id,
                s.student_fullname,
                c.class_name,
                AVG(er.marks_obtained) as avg_marks,
                COUNT(er.result_id) as total_exams,
                SUM(CASE WHEN er.grade IN ('A', 'A+') THEN 1 ELSE 0 END) as excellent_count,
                (SELECT COUNT(*) FROM student_attendance sa 
                 WHERE sa.student_id = s.student_id AND sa.status = 'present') as present_days,
                (SELECT COUNT(*) FROM student_attendance sa 
                 WHERE sa.student_id = s.student_id) as total_days
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN exam_results er ON s.student_id = er.student_id
            WHERE s.school_uid = ?
        ";
        
        if ($classId) {
            $query .= " AND s.class_id = ?";
        }
        
        $query .= " GROUP BY s.student_id ORDER BY avg_marks DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($classId) {
            $stmt->bind_param("ss", $schoolUid, $classId);
        } else {
            $stmt->bind_param("s", $schoolUid);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $performance = [];
        while ($row = $result->fetch_assoc()) {
            $attendanceRate = $row['total_days'] > 0 ? 
                round(($row['present_days'] / $row['total_days']) * 100, 2) : 0;
            
            $performance[] = [
                'student_id' => $row['student_id'],
                'student_name' => $row['student_fullname'],
                'class' => $row['class_name'],
                'average_marks' => round($row['avg_marks'], 2),
                'total_exams' => (int)$row['total_exams'],
                'excellent_grades' => (int)$row['excellent_count'],
                'attendance_rate' => $attendanceRate
            ];
        }
        
        Response::success($performance);
    }
    
    /**
     * Get teacher performance report
     * GET /api/v1/analytics/teacher-performance
     */
    public function getTeacherPerformance() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT 
                t.teacher_id,
                t.teacher_fullname,
                COUNT(DISTINCT ha.assignment_uid) as total_assignments,
                COUNT(DISTINCT es.exam_uid) as total_exams,
                AVG(er.marks_obtained) as avg_student_marks
            FROM teachers t
            LEFT JOIN homework_assignments ha ON t.teacher_id = ha.teacher_id
            LEFT JOIN exam_schedules es ON t.teacher_id = es.teacher_id
            LEFT JOIN exam_results er ON es.exam_uid = er.exam_uid
            WHERE t.School_unique_id = ?
            GROUP BY t.teacher_id
            ORDER BY t.teacher_fullname
        ");
        
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $performance = [];
        while ($row = $result->fetch_assoc()) {
            $performance[] = [
                'teacher_id' => $row['teacher_id'],
                'teacher_name' => $row['teacher_fullname'],
                'total_assignments' => (int)$row['total_assignments'],
                'total_exams' => (int)$row['total_exams'],
                'avg_student_marks' => round($row['avg_student_marks'], 2)
            ];
        }
        
        Response::success($performance);
    }
    
    /**
     * Get class performance comparison
     * GET /api/v1/analytics/class-comparison
     */
    public function getClassComparison() {
        $schoolUid = AuthMiddleware::getSchoolUid();
        
        $stmt = $this->conn->prepare("
            SELECT 
                c.class_id,
                c.class_name,
                COUNT(DISTINCT s.student_id) as student_count,
                AVG(er.marks_obtained) as avg_class_marks,
                SUM(CASE WHEN sa.status = 'present' THEN 1 ELSE 0 END) / 
                    NULLIF(COUNT(sa.attendance_id), 0) * 100 as attendance_rate
            FROM classes c
            LEFT JOIN students s ON c.class_id = s.class_id
            LEFT JOIN exam_results er ON s.student_id = er.student_id
            LEFT JOIN student_attendance sa ON s.student_id = sa.student_id
            WHERE c.school_unique_id = ?
            GROUP BY c.class_id
            ORDER BY c.class_name
        ");
        
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comparison = [];
        while ($row = $result->fetch_assoc()) {
            $comparison[] = [
                'class_id' => $row['class_id'],
                'class_name' => $row['class_name'],
                'student_count' => (int)$row['student_count'],
                'avg_marks' => round($row['avg_class_marks'], 2),
                'attendance_rate' => round($row['attendance_rate'], 2)
            ];
        }
        
        Response::success($comparison);
    }
    
    // Helper methods
    
    private function getStudentStats($schoolUid) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_today,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_this_month
            FROM students WHERE school_uid = ?
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getTeacherStats($schoolUid) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_this_month
            FROM teachers WHERE School_unique_id = ?
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getAssignmentStats($schoolUid) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN due_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming
            FROM homework_assignments WHERE school_uid = ?
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getAttendanceStats($schoolUid) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / 
                    NULLIF(COUNT(*), 0) * 100 as attendance_rate
            FROM student_attendance 
            WHERE school_uid = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getPerformanceTrends($schoolUid) {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE_FORMAT(es.exam_date, '%Y-%m') as month,
                AVG(er.marks_obtained) as avg_marks,
                COUNT(DISTINCT er.student_id) as students_count
            FROM exam_results er
            JOIN exam_schedules es ON er.exam_uid = es.exam_uid
            WHERE er.school_uid = ? AND es.exam_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month
            ORDER BY month
        ");
        $stmt->bind_param("s", $schoolUid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $trends[] = [
                'month' => $row['month'],
                'avg_marks' => round($row['avg_marks'], 2),
                'students' => (int)$row['students_count']
            ];
        }
        
        return $trends;
    }
}

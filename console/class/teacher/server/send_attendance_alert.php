<?php
session_start();
include_once "../../../../config.php";

if (!(isset($_SESSION["School_uid"]) && isset($_SESSION["teacher_email"]))) {
    echo "Access denied";
    exit();
}

$school_uid = $_SESSION["School_uid"];
$teacher_email = $_SESSION["teacher_email"];

// Get teacher ID
$teacher_query = mysqli_query($conn, "SELECT teacher_id FROM teachers WHERE teacher_email = '$teacher_email' AND School_unique_id = '$school_uid'");
$teacher = mysqli_fetch_array($teacher_query);
$teacher_id = $teacher['teacher_id'];

// Handle attendance alert request
if (isset($_POST['student_id']) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    
    if (sendAttendanceAlert($conn, $school_uid, $teacher_id, $student_id, $start_date, $end_date)) {
        echo "success";
    } else {
        echo "Failed to send attendance alert.";
    }
    exit();
}

function generateMessageUID() {
    return 'msg-' . bin2hex(random_bytes(8)) . '-' . time();
}

function sendAttendanceAlert($conn, $school_uid, $teacher_id, $student_id, $start_date, $end_date) {
    // Get student and attendance information
    $student_query = mysqli_query($conn, "SELECT s.*, 
                                         COUNT(sa.attendance_date) as present_days
                                         FROM students s
                                         LEFT JOIN student_attendance sa ON s.student_id = sa.student_id 
                                         AND sa.attendance_date BETWEEN '$start_date' AND '$end_date'
                                         AND sa.status = 1
                                         WHERE s.student_id = '$student_id' AND s.school_uid = '$school_uid'
                                         GROUP BY s.student_id");
    
    if (mysqli_num_rows($student_query) == 0) {
        return false;
    }
    
    $student = mysqli_fetch_array($student_query);
    $total_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
    $attendance_percentage = $total_days > 0 ? ($student['present_days'] / $total_days) * 100 : 0;
    
    // Generate automated message
    $message_uid = generateMessageUID();
    $subject = "Attendance Alert for " . $student['student_first_name'] . " " . $student['student_last_name'];
    $message_body = "Dear Parent,\n\n";
    $message_body .= "This is an automated attendance alert for your child " . $student['student_first_name'] . " " . $student['student_last_name'] . ".\n\n";
    $message_body .= "Attendance Summary (". date('M d', strtotime($start_date)) . " - " . date('M d', strtotime($end_date)) . "):\n";
    $message_body .= "- Present Days: " . $student['present_days'] . "\n";
    $message_body .= "- Total School Days: $total_days\n";
    $message_body .= "- Attendance Percentage: " . round($attendance_percentage, 1) . "%\n\n";
    
    if ($attendance_percentage < 75) {
        $message_body .= "⚠️ WARNING: Your child's attendance is below the required 75% threshold. ";
        $message_body .= "Please ensure regular attendance to avoid academic difficulties.\n\n";
    }
    
    $message_body .= "If you have any concerns about your child's attendance, please contact us.\n\n";
    $message_body .= "Best regards,\nClass Teacher";
    
    $recipient_id = 'parent-' . $student_id;
    
    $query = "INSERT INTO messages 
              (message_uid, school_uid, sender_type, sender_id, recipient_type, recipient_id, student_id, subject, message_body, message_type, is_urgent) 
              VALUES 
              ('$message_uid', '$school_uid', 'teacher', '$teacher_id', 'parent', '$recipient_id', '$student_id', '$subject', '$message_body', 'attendance', 1)";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        // Send email notification if parent email exists
        if (!empty($student['parent_email']) && filter_var($student['parent_email'], FILTER_VALIDATE_EMAIL)) {
            sendEmailNotification($student['parent_email'], $subject, $message_body, $school_uid);
        }
        return true;
    }
    
    return false;
}

function sendEmailNotification($email_to, $subject, $message_body, $school_uid) {
    // Get school details
    $school_query = mysqli_query($GLOBALS['conn'], "SELECT School_name FROM schools WHERE unique_id = '$school_uid'");
    $school = mysqli_fetch_array($school_query);
    $school_name = $school['School_name'];
    
    $email_subject = "Attendance Alert from " . $school_name;
    $email_headers = "From: noreply@ekilie.com\r\n";
    $email_headers .= "Reply-To: noreply@ekilie.com\r\n";
    $email_headers .= "X-Mailer: PHP/" . phpversion();
    
    // Note: In production, you'd want to use a proper email service
    // mail($email_to, $email_subject, $message_body, $email_headers);
}
?>
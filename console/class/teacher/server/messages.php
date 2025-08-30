<?php
session_start();
include_once "../../../../config.php";

if (!(isset($_SESSION["School_uid"]) && isset($_SESSION["teacher_email"]))) {
    echo "Access denied";
    exit();
}

$formType = $_POST["form-type"];
$school_uid = $_SESSION["School_uid"];
$teacher_email = $_SESSION["teacher_email"];

// Get teacher ID
$teacher_query = mysqli_query($conn, "SELECT teacher_id FROM teachers WHERE teacher_email = '$teacher_email' AND School_unique_id = '$school_uid'");
$teacher = mysqli_fetch_array($teacher_query);
$teacher_id = $teacher['teacher_id'];

switch($formType) {
    case 'send-message':
        sendMessage($conn, $school_uid, $teacher_id);
        break;
    case 'mark-read':
        markMessageRead($conn, $school_uid, $teacher_id);
        break;
    case 'delete-message':
        deleteMessage($conn, $school_uid, $teacher_id);
        break;
    default:
        echo "Invalid form type!";
        break;
}

function generateMessageUID() {
    return 'msg-' . bin2hex(random_bytes(8)) . '-' . time();
}

function sendMessage($conn, $school_uid, $teacher_id) {
    // Sanitize and validate input
    $recipient_type = mysqli_real_escape_string($conn, $_POST['recipient_type']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message_body = mysqli_real_escape_string($conn, $_POST['message_body']);
    $message_type = mysqli_real_escape_string($conn, $_POST['message_type']);
    $is_urgent = isset($_POST['is_urgent']) ? 1 : 0;
    
    // Generate unique message ID
    $message_uid = generateMessageUID();
    
    // Validate required fields
    if (empty($subject) || empty($message_body) || empty($recipient_type)) {
        echo "Please fill in all required fields.";
        return;
    }
    
    // Determine recipient ID based on type
    $recipient_id = '';
    if ($recipient_type == 'parent') {
        if (empty($student_id)) {
            echo "Please select a student when sending to parents.";
            return;
        }
        
        // For now, we'll use a placeholder parent ID. In a real system, 
        // you'd have a parents table linked to students
        $recipient_id = 'parent-' . $student_id;
        
        // Verify student belongs to this class
        $student_check = mysqli_query($conn, "SELECT student_id FROM students 
                                            WHERE student_id = '$student_id' 
                                            AND school_uid = '$school_uid'");
        if (mysqli_num_rows($student_check) == 0) {
            echo "Invalid student selected.";
            return;
        }
        
    } elseif ($recipient_type == 'admin') {
        $recipient_id = 'admin-' . $school_uid;
        $student_id = NULL; // No specific student for admin messages
    } else {
        echo "Invalid recipient type.";
        return;
    }
    
    // Insert message
    $query = "INSERT INTO messages 
              (message_uid, school_uid, sender_type, sender_id, recipient_type, recipient_id, student_id, subject, message_body, message_type, is_urgent) 
              VALUES 
              ('$message_uid', '$school_uid', 'teacher', '$teacher_id', '$recipient_type', '$recipient_id', " . 
              ($student_id ? "'$student_id'" : "NULL") . ", '$subject', '$message_body', '$message_type', '$is_urgent')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
        
        // TODO: Send email notification to recipient
        // TODO: Send push notification if mobile app exists
        
        // For now, we can send a simple email notification
        sendEmailNotification($conn, $recipient_type, $recipient_id, $subject, $message_body, $school_uid, $student_id);
        
    } else {
        echo "Something went wrong. Please try again. " . mysqli_error($conn);
    }
}

function sendEmailNotification($conn, $recipient_type, $recipient_id, $subject, $message_body, $school_uid, $student_id) {
    // Get school details
    $school_query = mysqli_query($conn, "SELECT School_name FROM schools WHERE unique_id = '$school_uid'");
    $school = mysqli_fetch_array($school_query);
    $school_name = $school['School_name'];
    
    $email_to = '';
    $recipient_name = '';
    
    if ($recipient_type == 'parent' && $student_id) {
        // Get parent email from student record
        $student_query = mysqli_query($conn, "SELECT parent_email, student_first_name, student_last_name 
                                            FROM students WHERE student_id = '$student_id'");
        if ($student_query && mysqli_num_rows($student_query) > 0) {
            $student = mysqli_fetch_array($student_query);
            $email_to = $student['parent_email'];
            $recipient_name = "Parent of " . $student['student_first_name'] . " " . $student['student_last_name'];
        }
    }
    
    // Only send email if we have a valid email address
    if (!empty($email_to) && filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
        $email_subject = "Message from " . $school_name . ": " . $subject;
        $email_body = "Dear $recipient_name,\n\n";
        $email_body .= "You have received a new message from your child's teacher:\n\n";
        $email_body .= "Subject: $subject\n\n";
        $email_body .= "Message:\n" . $message_body . "\n\n";
        $email_body .= "Please log in to the ekiliSense portal to respond.\n\n";
        $email_body .= "Best regards,\n$school_name";
        
        $headers = "From: noreply@ekilie.com\r\n";
        $headers .= "Reply-To: noreply@ekilie.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Note: In production, you'd want to use a proper email service like SendGrid, Mailgun, etc.
        // mail($email_to, $email_subject, $email_body, $headers);
    }
}

function markMessageRead($conn, $school_uid, $teacher_id) {
    $message_uid = mysqli_real_escape_string($conn, $_POST['message_uid']);
    
    // Verify teacher has permission to mark this message as read
    $verify_query = mysqli_query($conn, "SELECT message_id FROM messages 
                                       WHERE message_uid = '$message_uid' 
                                       AND recipient_type = 'teacher' 
                                       AND recipient_id = '$teacher_id'
                                       AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to modify this message.";
        return;
    }
    
    $query = "UPDATE messages SET is_read = 1, read_at = NOW() 
              WHERE message_uid = '$message_uid' 
              AND recipient_type = 'teacher' 
              AND recipient_id = '$teacher_id'
              AND school_uid = '$school_uid'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

function deleteMessage($conn, $school_uid, $teacher_id) {
    $message_uid = mysqli_real_escape_string($conn, $_POST['message_uid']);
    
    // Verify teacher has permission to delete this message (either sender or recipient)
    $verify_query = mysqli_query($conn, "SELECT message_id FROM messages 
                                       WHERE message_uid = '$message_uid' 
                                       AND ((sender_type = 'teacher' AND sender_id = '$teacher_id') 
                                            OR (recipient_type = 'teacher' AND recipient_id = '$teacher_id'))
                                       AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to delete this message.";
        return;
    }
    
    $query = "DELETE FROM messages 
              WHERE message_uid = '$message_uid' 
              AND school_uid = '$school_uid'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

// Function to send automated attendance alerts
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
    $attendance_percentage = ($student['present_days'] / $total_days) * 100;
    
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
        // Send email notification
        sendEmailNotification($conn, 'parent', $recipient_id, $subject, $message_body, $school_uid, $student_id);
        return true;
    }
    
    return false;
}

// Handle attendance alert requests
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
?>
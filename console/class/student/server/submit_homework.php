<?php
session_start();
include_once "../../../../config.php";

if (!(isset($_SESSION["School_uid"]) && isset($_SESSION["student_email"]))) {
    echo "Access denied";
    exit();
}

$school_uid = $_SESSION["School_uid"];
$student_email = $_SESSION["student_email"];

// Get student ID
$student_query = mysqli_query($conn, "SELECT student_id FROM students WHERE student_email = '$student_email' AND school_uid = '$school_uid'");
if (mysqli_num_rows($student_query) == 0) {
    echo "Student not found";
    exit();
}
$student = mysqli_fetch_array($student_query);
$student_id = $student['student_id'];

// Get form data
$assignment_uid = mysqli_real_escape_string($conn, $_POST['assignment_uid']);
$submission_text = mysqli_real_escape_string($conn, $_POST['submission_text']);
$assignment_type = mysqli_real_escape_string($conn, $_POST['assignment_type']);

// Validate required fields
if (empty($assignment_uid) || empty($submission_text)) {
    echo "Please fill in all required fields.";
    exit();
}

// Verify assignment exists and is active
$assignment_query = mysqli_query($conn, "SELECT * FROM homework_assignments 
    WHERE assignment_uid = '$assignment_uid' 
    AND school_uid = '$school_uid' 
    AND status = 'active'");

if (mysqli_num_rows($assignment_query) == 0) {
    echo "Assignment not found or no longer active.";
    exit();
}

$assignment = mysqli_fetch_array($assignment_query);

// Check if student already submitted
$check_submission = mysqli_query($conn, "SELECT submission_id FROM homework_submissions 
    WHERE assignment_uid = '$assignment_uid' 
    AND student_id = '$student_id'");

if (mysqli_num_rows($check_submission) > 0) {
    echo "You have already submitted this homework.";
    exit();
}

// Handle file upload
$file_name = null;
$file_path = null;

if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['file_upload'];
    
    // Validate file size (5MB max)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        echo "File size must be less than 5MB.";
        exit();
    }
    
    // Validate file type
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'image/jpeg', 'image/jpg', 'image/png'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        echo "Invalid file type. Only PDF, DOC, DOCX, TXT, JPG, and PNG files are allowed.";
        exit();
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = "../../../../uploads/homework_submissions/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = $student_id . '_' . $assignment_uid . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $unique_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $file_name = $file['name'];
        $file_path = $upload_path;
    } else {
        echo "Failed to upload file. Please try again.";
        exit();
    }
}

// Check if submission is late
$due_datetime = $assignment['due_date'] . ' ' . ($assignment['due_time'] ?? '23:59:59');
$is_late = date('Y-m-d H:i:s') > $due_datetime;
$submission_status = $is_late ? 'late' : 'submitted';

// Auto-grade quiz if applicable
$auto_grade = null;
if ($assignment_type === 'quiz') {
    // For quiz type assignments, we would implement auto-grading logic here
    // This is a placeholder for the auto-grading feature
    // In a real implementation, this would parse quiz answers and calculate grade
    $auto_grade = calculateQuizGrade($submission_text, $assignment);
}

// Insert submission
$query = "INSERT INTO homework_submissions 
          (assignment_uid, student_id, school_uid, submission_text, file_name, file_path, status, grade) 
          VALUES 
          ('$assignment_uid', '$student_id', '$school_uid', '$submission_text', " . 
          ($file_name ? "'$file_name'" : "NULL") . ", " . 
          ($file_path ? "'$file_path'" : "NULL") . ", " . 
          ($auto_grade !== null ? "'graded'" : "'$submission_status'") . ", " . 
          ($auto_grade !== null ? "$auto_grade" : "NULL") . ")";

$result = mysqli_query($conn, $query);

if ($result) {
    echo "success";
} else {
    echo "Something went wrong. Please try again. " . mysqli_error($conn);
}

// Function to calculate quiz grade (placeholder for auto-grading)
function calculateQuizGrade($submission_text, $assignment) {
    // This is a basic implementation for auto-grading
    // In a real implementation, this would be more sophisticated
    
    // For now, we'll return null to indicate manual grading is needed
    // A full implementation would:
    // 1. Parse quiz questions and correct answers from assignment description
    // 2. Parse student answers from submission_text
    // 3. Compare answers and calculate grade
    // 4. Return the calculated grade
    
    return null; // Return null for now - manual grading will be used
}
?>

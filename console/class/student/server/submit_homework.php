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

// Insert submission first to get submission_id
$query = "INSERT INTO homework_submissions 
          (assignment_uid, student_id, school_uid, submission_text, file_name, file_path, status) 
          VALUES 
          ('$assignment_uid', '$student_id', '$school_uid', '$submission_text', " . 
          ($file_name ? "'$file_name'" : "NULL") . ", " . 
          ($file_path ? "'$file_path'" : "NULL") . ", " . 
          "'$submission_status')";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "Something went wrong. Please try again. " . mysqli_error($conn);
    exit();
}

$submission_id = mysqli_insert_id($conn);

// Auto-grade quiz if applicable
if ($assignment_type === 'quiz') {
    $auto_grade = autoGradeQuiz($conn, $submission_id, $assignment_uid, $submission_text, $assignment);
    
    if ($auto_grade !== null) {
        // Update submission with auto-grade
        $update_query = "UPDATE homework_submissions 
                        SET grade = $auto_grade, status = 'graded', graded_at = NOW() 
                        WHERE submission_id = $submission_id";
        mysqli_query($conn, $update_query);
    }
}

echo "success";

// Function to auto-grade quiz submissions
function autoGradeQuiz($conn, $submission_id, $assignment_uid, $submission_text, $assignment) {
    // Check if quiz questions exist for this assignment
    $questions_query = mysqli_query($conn, "SELECT * FROM quiz_questions 
        WHERE assignment_uid = '$assignment_uid' 
        ORDER BY question_order ASC");
    
    if (mysqli_num_rows($questions_query) == 0) {
        // No quiz questions defined, cannot auto-grade
        return null;
    }
    
    // Parse student answers from submission text
    // Expected format: "1:A|2:B|3:C" or as JSON
    $student_answers = parseQuizAnswers($submission_text);
    
    if (empty($student_answers)) {
        // Cannot parse answers, manual grading needed
        return null;
    }
    
    $total_points = 0;
    $earned_points = 0;
    
    while ($question = mysqli_fetch_array($questions_query)) {
        $question_id = $question['question_id'];
        $correct_answer = strtolower(trim($question['correct_answer']));
        $points = $question['points'];
        $total_points += $points;
        
        // Get student answer for this question
        $student_answer = isset($student_answers[$question_id]) 
            ? strtolower(trim($student_answers[$question_id])) 
            : '';
        
        $is_correct = false;
        $points_earned = 0;
        
        // Check if answer is correct
        if ($question['question_type'] == 'multiple_choice' || $question['question_type'] == 'true_false') {
            // Exact match for multiple choice and true/false
            $is_correct = ($student_answer === $correct_answer);
        } else {
            // Partial match for short answers (case-insensitive)
            $is_correct = (strpos($student_answer, $correct_answer) !== false || 
                          strpos($correct_answer, $student_answer) !== false);
        }
        
        if ($is_correct) {
            $points_earned = $points;
            $earned_points += $points;
        }
        
        // Store individual answer
        $answer_query = "INSERT INTO quiz_answers 
                        (submission_id, question_id, student_answer, is_correct, points_earned) 
                        VALUES 
                        ($submission_id, $question_id, '$student_answer', " . 
                        ($is_correct ? '1' : '0') . ", $points_earned)";
        mysqli_query($conn, $answer_query);
    }
    
    // Calculate final grade based on max_points from assignment
    if ($total_points > 0) {
        $max_points = $assignment['max_points'];
        $grade = round(($earned_points / $total_points) * $max_points);
        return $grade;
    }
    
    return null;
}

// Function to parse quiz answers from submission text
function parseQuizAnswers($submission_text) {
    $answers = [];
    
    // Try to parse as JSON first
    $json_data = json_decode($submission_text, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
        return $json_data;
    }
    
    // Try to parse as "question_id:answer|question_id:answer" format
    $parts = explode('|', $submission_text);
    foreach ($parts as $part) {
        if (strpos($part, ':') !== false) {
            list($question_id, $answer) = explode(':', $part, 2);
            $answers[trim($question_id)] = trim($answer);
        }
    }
    
    return $answers;
}
?>

<?php
session_start();
include_once "../../../../config.php";

header('Content-Type: application/json');

if (!(isset($_SESSION["School_uid"]) && isset($_SESSION["student_email"]))) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$school_uid = $_SESSION["School_uid"];
$student_email = $_SESSION["student_email"];

// Get student ID
$student_query = mysqli_query($conn, "SELECT student_id FROM students WHERE student_email = '$student_email' AND school_uid = '$school_uid'");
if (mysqli_num_rows($student_query) == 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit();
}
$student = mysqli_fetch_array($student_query);
$student_id = $student['student_id'];

if (!isset($_GET['assignment_uid'])) {
    echo json_encode(['success' => false, 'message' => 'Assignment ID required']);
    exit();
}

$assignment_uid = mysqli_real_escape_string($conn, $_GET['assignment_uid']);

// Get submission details
$query = "SELECT hs.*, ha.title as assignment_title, ha.max_points
          FROM homework_submissions hs
          JOIN homework_assignments ha ON hs.assignment_uid = ha.assignment_uid
          WHERE hs.assignment_uid = '$assignment_uid'
          AND hs.student_id = '$student_id'
          AND hs.school_uid = '$school_uid'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Submission not found']);
    exit();
}

$submission = mysqli_fetch_array($result);

// Format the submission data
$response = [
    'success' => true,
    'submission' => [
        'submission_id' => $submission['submission_id'],
        'assignment_title' => $submission['assignment_title'],
        'submission_text' => $submission['submission_text'],
        'file_name' => $submission['file_name'],
        'file_path' => $submission['file_path'],
        'submission_date' => date('M d, Y h:i A', strtotime($submission['submission_date'])),
        'grade' => $submission['grade'],
        'max_points' => $submission['max_points'],
        'teacher_feedback' => $submission['teacher_feedback'],
        'status' => ucfirst($submission['status'])
    ]
];

echo json_encode($response);
?>

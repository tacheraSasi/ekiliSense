<?php
session_start();
include_once "../../../../config.php";

header('Content-Type: application/json');

if (!(isset($_SESSION["School_uid"]) && isset($_SESSION["teacher_email"]))) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$school_uid = $_SESSION["School_uid"];
$teacher_email = $_SESSION["teacher_email"];

// Get teacher ID
$teacher_query = mysqli_query($conn, "SELECT teacher_id FROM teachers WHERE teacher_email = '$teacher_email' AND School_unique_id = '$school_uid'");
$teacher = mysqli_fetch_array($teacher_query);
$teacher_id = $teacher['teacher_id'];

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Submission ID required']);
    exit();
}

$submission_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get submission details with permission check
$query = "SELECT hs.*, st.student_name, st.student_email, ha.assignment_uid, ha.max_points
          FROM homework_submissions hs
          LEFT JOIN students st ON hs.student_id = st.student_id
          JOIN homework_assignments ha ON hs.assignment_uid = ha.assignment_uid
          WHERE hs.submission_id = '$submission_id'
          AND ha.teacher_id = '$teacher_id'
          AND hs.school_uid = '$school_uid'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Submission not found or access denied']);
    exit();
}

$submission = mysqli_fetch_array($result);

// Format the submission data
$response = [
    'success' => true,
    'submission' => [
        'submission_id' => $submission['submission_id'],
        'student_name' => $submission['student_name'],
        'student_email' => $submission['student_email'],
        'submission_text' => $submission['submission_text'],
        'file_name' => $submission['file_name'],
        'file_path' => $submission['file_path'],
        'submission_date' => date('M d, Y h:i A', strtotime($submission['submission_date'])),
        'grade' => $submission['grade'],
        'max_points' => $submission['max_points'],
        'teacher_feedback' => $submission['teacher_feedback'],
        'status' => $submission['status']
    ]
];

echo json_encode($response);
?>

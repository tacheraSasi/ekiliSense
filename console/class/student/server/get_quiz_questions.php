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

// Verify assignment exists and is accessible
$assignment_query = mysqli_query($conn, "SELECT * FROM homework_assignments 
    WHERE assignment_uid = '$assignment_uid' 
    AND school_uid = '$school_uid' 
    AND status = 'active'");

if (mysqli_num_rows($assignment_query) == 0) {
    echo json_encode(['success' => false, 'message' => 'Assignment not found or no longer active']);
    exit();
}

// Get quiz questions (hide correct answers from students)
$questions_query = mysqli_query($conn, "SELECT question_id, question_text, question_type, 
    option_a, option_b, option_c, option_d, points, question_order 
    FROM quiz_questions 
    WHERE assignment_uid = '$assignment_uid' 
    ORDER BY question_order ASC");

$questions = [];
while ($question = mysqli_fetch_array($questions_query)) {
    $questions[] = [
        'question_id' => $question['question_id'],
        'question_text' => $question['question_text'],
        'question_type' => $question['question_type'],
        'option_a' => $question['option_a'],
        'option_b' => $question['option_b'],
        'option_c' => $question['option_c'],
        'option_d' => $question['option_d'],
        'points' => $question['points']
    ];
}

echo json_encode([
    'success' => true,
    'questions' => $questions
]);
?>

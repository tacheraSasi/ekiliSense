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
    case 'add-question':
        addQuestion($conn, $school_uid, $teacher_id);
        break;
    case 'delete-question':
        deleteQuestion($conn, $school_uid, $teacher_id);
        break;
    default:
        echo "Invalid form type!";
        break;
}

function addQuestion($conn, $school_uid, $teacher_id) {
    $assignment_uid = mysqli_real_escape_string($conn, $_POST['assignment_uid']);
    $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $question_type = mysqli_real_escape_string($conn, $_POST['question_type']);
    $correct_answer = mysqli_real_escape_string($conn, $_POST['correct_answer']);
    $points = mysqli_real_escape_string($conn, $_POST['points']);
    
    // Optional fields for multiple choice
    $option_a = isset($_POST['option_a']) ? mysqli_real_escape_string($conn, $_POST['option_a']) : null;
    $option_b = isset($_POST['option_b']) ? mysqli_real_escape_string($conn, $_POST['option_b']) : null;
    $option_c = isset($_POST['option_c']) ? mysqli_real_escape_string($conn, $_POST['option_c']) : null;
    $option_d = isset($_POST['option_d']) ? mysqli_real_escape_string($conn, $_POST['option_d']) : null;
    
    // Verify teacher owns this assignment
    $verify_query = mysqli_query($conn, "SELECT assignment_id FROM homework_assignments 
                                       WHERE assignment_uid = '$assignment_uid' 
                                       AND teacher_id = '$teacher_id' 
                                       AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to add questions to this assignment.";
        return;
    }
    
    // Validate required fields
    if (empty($question_text) || empty($question_type) || empty($correct_answer) || empty($points)) {
        echo "Please fill in all required fields.";
        return;
    }
    
    // Validate points
    if (!is_numeric($points) || $points < 1) {
        echo "Points must be a positive number.";
        return;
    }
    
    // Get next question order
    $order_query = mysqli_query($conn, "SELECT MAX(question_order) as max_order FROM quiz_questions 
                                       WHERE assignment_uid = '$assignment_uid'");
    $order_result = mysqli_fetch_array($order_query);
    $next_order = ($order_result['max_order'] ?? 0) + 1;
    
    // Build the query
    $query = "INSERT INTO quiz_questions 
              (assignment_uid, question_text, question_type, correct_answer, option_a, option_b, option_c, option_d, points, question_order) 
              VALUES 
              ('$assignment_uid', '$question_text', '$question_type', '$correct_answer', " . 
              ($option_a ? "'$option_a'" : "NULL") . ", " . 
              ($option_b ? "'$option_b'" : "NULL") . ", " . 
              ($option_c ? "'$option_c'" : "NULL") . ", " . 
              ($option_d ? "'$option_d'" : "NULL") . ", " . 
              "$points, $next_order)";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again. " . mysqli_error($conn);
    }
}

function deleteQuestion($conn, $school_uid, $teacher_id) {
    $question_id = mysqli_real_escape_string($conn, $_POST['question_id']);
    
    // Verify teacher owns this question's assignment
    $verify_query = mysqli_query($conn, "SELECT qq.question_id FROM quiz_questions qq
                                       JOIN homework_assignments ha ON qq.assignment_uid = ha.assignment_uid
                                       WHERE qq.question_id = '$question_id' 
                                       AND ha.teacher_id = '$teacher_id' 
                                       AND ha.school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to delete this question.";
        return;
    }
    
    // Delete the question
    $query = "DELETE FROM quiz_questions WHERE question_id = '$question_id'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}
?>

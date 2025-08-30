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
    case 'add-homework':
        addHomework($conn, $school_uid, $teacher_id);
        break;
    case 'update-homework':
        updateHomework($conn, $school_uid, $teacher_id);
        break;
    case 'delete-homework':
        deleteHomework($conn, $school_uid, $teacher_id);
        break;
    case 'grade-submission':
        gradeSubmission($conn, $school_uid, $teacher_id);
        break;
    default:
        echo "Invalid form type!";
        break;
}

function generateHomeworkUID() {
    return 'hw-' . bin2hex(random_bytes(8)) . '-' . time();
}

function addHomework($conn, $school_uid, $teacher_id) {
    // Sanitize and validate input
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $due_time = mysqli_real_escape_string($conn, $_POST['due_time']);
    $assignment_type = mysqli_real_escape_string($conn, $_POST['assignment_type']);
    $max_points = mysqli_real_escape_string($conn, $_POST['max_points']);
    
    // Generate unique assignment ID
    $assignment_uid = generateHomeworkUID();
    
    // Validate required fields
    if (empty($title) || empty($due_date) || empty($subject_id)) {
        echo "Please fill in all required fields.";
        return;
    }
    
    // Validate due date is not in the past
    if ($due_date < date('Y-m-d')) {
        echo "Due date cannot be in the past.";
        return;
    }
    
    // Build the query
    $query = "INSERT INTO homework_assignments 
              (assignment_uid, school_uid, class_id, subject_id, teacher_id, title, description, due_date, due_time, max_points, assignment_type) 
              VALUES 
              ('$assignment_uid', '$school_uid', '$class_id', '$subject_id', '$teacher_id', '$title', '$description', '$due_date', " . 
              ($due_time ? "'$due_time'" : "NULL") . ", '$max_points', '$assignment_type')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
        
        // TODO: Send notifications to students/parents about new homework
        // This can be implemented later with email notifications
        
    } else {
        echo "Something went wrong. Please try again. " . mysqli_error($conn);
    }
}

function updateHomework($conn, $school_uid, $teacher_id) {
    $assignment_uid = mysqli_real_escape_string($conn, $_POST['assignment_uid']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $due_time = mysqli_real_escape_string($conn, $_POST['due_time']);
    $assignment_type = mysqli_real_escape_string($conn, $_POST['assignment_type']);
    $max_points = mysqli_real_escape_string($conn, $_POST['max_points']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Verify teacher owns this assignment
    $verify_query = mysqli_query($conn, "SELECT assignment_id FROM homework_assignments 
                                       WHERE assignment_uid = '$assignment_uid' 
                                       AND teacher_id = '$teacher_id' 
                                       AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to edit this assignment.";
        return;
    }
    
    $query = "UPDATE homework_assignments SET 
              title = '$title',
              description = '$description',
              due_date = '$due_date',
              due_time = " . ($due_time ? "'$due_time'" : "NULL") . ",
              assignment_type = '$assignment_type',
              max_points = '$max_points',
              status = '$status'
              WHERE assignment_uid = '$assignment_uid' 
              AND teacher_id = '$teacher_id' 
              AND school_uid = '$school_uid'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

function deleteHomework($conn, $school_uid, $teacher_id) {
    $assignment_uid = mysqli_real_escape_string($conn, $_POST['assignment_uid']);
    
    // Verify teacher owns this assignment
    $verify_query = mysqli_query($conn, "SELECT assignment_id FROM homework_assignments 
                                       WHERE assignment_uid = '$assignment_uid' 
                                       AND teacher_id = '$teacher_id' 
                                       AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to delete this assignment.";
        return;
    }
    
    // Delete the assignment (submissions will be deleted automatically due to foreign key constraint)
    $query = "DELETE FROM homework_assignments 
              WHERE assignment_uid = '$assignment_uid' 
              AND teacher_id = '$teacher_id' 
              AND school_uid = '$school_uid'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

function gradeSubmission($conn, $school_uid, $teacher_id) {
    $submission_id = mysqli_real_escape_string($conn, $_POST['submission_id']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    // Validate grade
    if (!is_numeric($grade) || $grade < 0) {
        echo "Please enter a valid grade.";
        return;
    }
    
    // Verify teacher has permission to grade this submission
    $verify_query = mysqli_query($conn, "SELECT hs.submission_id, ha.max_points 
                                       FROM homework_submissions hs
                                       JOIN homework_assignments ha ON hs.assignment_uid = ha.assignment_uid
                                       WHERE hs.submission_id = '$submission_id' 
                                       AND ha.teacher_id = '$teacher_id' 
                                       AND hs.school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to grade this submission.";
        return;
    }
    
    $submission_data = mysqli_fetch_array($verify_query);
    $max_points = $submission_data['max_points'];
    
    // Validate grade doesn't exceed max points
    if ($grade > $max_points) {
        echo "Grade cannot exceed maximum points ($max_points).";
        return;
    }
    
    $query = "UPDATE homework_submissions SET 
              grade = '$grade',
              teacher_feedback = '$feedback',
              status = 'graded',
              graded_at = NOW()
              WHERE submission_id = '$submission_id'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
        
        // TODO: Send notification to student/parent about graded homework
        
    } else {
        echo "Something went wrong. Please try again.";
    }
}
?>
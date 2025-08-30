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
    case 'add-exam':
        addExam($conn, $school_uid, $teacher_id);
        break;
    case 'update-exam':
        updateExam($conn, $school_uid, $teacher_id);
        break;
    case 'delete-exam':
        deleteExam($conn, $school_uid, $teacher_id);
        break;
    case 'mark-completed':
        markExamCompleted($conn, $school_uid, $teacher_id);
        break;
    case 'add-result':
        addExamResult($conn, $school_uid, $teacher_id);
        break;
    case 'update-result':
        updateExamResult($conn, $school_uid, $teacher_id);
        break;
    default:
        echo "Invalid form type!";
        break;
}

function generateExamUID() {
    return 'exam-' . bin2hex(random_bytes(8)) . '-' . time();
}

function addExam($conn, $school_uid, $teacher_id) {
    // Sanitize and validate input
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
    $exam_title = mysqli_real_escape_string($conn, $_POST['exam_title']);
    $exam_description = mysqli_real_escape_string($conn, $_POST['exam_description']);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $exam_type = mysqli_real_escape_string($conn, $_POST['exam_type']);
    $max_marks = mysqli_real_escape_string($conn, $_POST['max_marks']);
    
    // Generate unique exam ID
    $exam_uid = generateExamUID();
    
    // Validate required fields
    if (empty($exam_title) || empty($exam_date) || empty($start_time) || empty($end_time) || empty($subject_id)) {
        echo "Please fill in all required fields.";
        return;
    }
    
    // Validate exam date is not in the past
    if ($exam_date < date('Y-m-d')) {
        echo "Exam date cannot be in the past.";
        return;
    }
    
    // Validate time order
    if ($start_time >= $end_time) {
        echo "Start time must be before end time.";
        return;
    }
    
    // Check for scheduling conflicts
    $conflict_query = mysqli_query($conn, "SELECT exam_title FROM exam_schedules 
                                         WHERE school_uid = '$school_uid' 
                                         AND class_id = '$class_id' 
                                         AND exam_date = '$exam_date' 
                                         AND status != 'cancelled'
                                         AND (
                                             ('$start_time' BETWEEN start_time AND end_time) OR
                                             ('$end_time' BETWEEN start_time AND end_time) OR
                                             (start_time BETWEEN '$start_time' AND '$end_time') OR
                                             (end_time BETWEEN '$start_time' AND '$end_time')
                                         )");
    
    if (mysqli_num_rows($conflict_query) > 0) {
        $conflict = mysqli_fetch_array($conflict_query);
        echo "Time conflict with existing exam: " . $conflict['exam_title'];
        return;
    }
    
    // Build the query
    $query = "INSERT INTO exam_schedules 
              (exam_uid, school_uid, class_id, subject_id, teacher_id, exam_title, exam_description, exam_date, start_time, end_time, exam_type, max_marks) 
              VALUES 
              ('$exam_uid', '$school_uid', '$class_id', '$subject_id', '$teacher_id', '$exam_title', '$exam_description', '$exam_date', '$start_time', '$end_time', '$exam_type', '$max_marks')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
        
        // TODO: Send notifications to students/parents about new exam
        
    } else {
        echo "Something went wrong. Please try again. " . mysqli_error($conn);
    }
}

function updateExam($conn, $school_uid, $teacher_id) {
    $exam_uid = mysqli_real_escape_string($conn, $_POST['exam_uid']);
    $exam_title = mysqli_real_escape_string($conn, $_POST['exam_title']);
    $exam_description = mysqli_real_escape_string($conn, $_POST['exam_description']);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $exam_type = mysqli_real_escape_string($conn, $_POST['exam_type']);
    $max_marks = mysqli_real_escape_string($conn, $_POST['max_marks']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Verify teacher owns this exam
    $verify_query = mysqli_query($conn, "SELECT exam_id FROM exam_schedules 
                                       WHERE exam_uid = '$exam_uid' 
                                       AND teacher_id = '$teacher_id' 
                                       AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to edit this exam.";
        return;
    }
    
    $query = "UPDATE exam_schedules SET 
              exam_title = '$exam_title',
              exam_description = '$exam_description',
              exam_date = '$exam_date',
              start_time = '$start_time',
              end_time = '$end_time',
              exam_type = '$exam_type',
              max_marks = '$max_marks',
              status = '$status'
              WHERE exam_uid = '$exam_uid' 
              AND teacher_id = '$teacher_id' 
              AND school_uid = '$school_uid'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

function markExamCompleted($conn, $school_uid, $teacher_id) {
    $exam_uid = mysqli_real_escape_string($conn, $_POST['exam_uid']);
    
    // Verify teacher owns this exam
    $verify_query = mysqli_query($conn, "SELECT exam_id FROM exam_schedules 
                                       WHERE exam_uid = '$exam_uid' 
                                       AND teacher_id = '$teacher_id' 
                                       AND school_uid = '$school_uid'
                                       AND status = 'scheduled'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "Exam not found or cannot be marked as completed.";
        return;
    }
    
    $query = "UPDATE exam_schedules SET status = 'completed' 
              WHERE exam_uid = '$exam_uid' 
              AND teacher_id = '$teacher_id' 
              AND school_uid = '$school_uid'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

function addExamResult($conn, $school_uid, $teacher_id) {
    $exam_uid = mysqli_real_escape_string($conn, $_POST['exam_uid']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $marks_obtained = mysqli_real_escape_string($conn, $_POST['marks_obtained']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    // Verify teacher owns this exam
    $exam_query = mysqli_query($conn, "SELECT max_marks FROM exam_schedules 
                                     WHERE exam_uid = '$exam_uid' 
                                     AND teacher_id = '$teacher_id' 
                                     AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($exam_query) == 0) {
        echo "You don't have permission to add results for this exam.";
        return;
    }
    
    $exam_data = mysqli_fetch_array($exam_query);
    $max_marks = $exam_data['max_marks'];
    
    // Validate marks
    if (!is_numeric($marks_obtained) || $marks_obtained < 0 || $marks_obtained > $max_marks) {
        echo "Marks must be between 0 and $max_marks.";
        return;
    }
    
    // Calculate grade
    $percentage = ($marks_obtained / $max_marks) * 100;
    $grade = calculateGrade($percentage);
    
    // Check if result already exists
    $check_query = mysqli_query($conn, "SELECT result_id FROM exam_results 
                                      WHERE exam_uid = '$exam_uid' 
                                      AND student_id = '$student_id'");
    
    if (mysqli_num_rows($check_query) > 0) {
        echo "Result already exists for this student. Use update instead.";
        return;
    }
    
    $query = "INSERT INTO exam_results 
              (exam_uid, student_id, school_uid, marks_obtained, grade, remarks, teacher_id) 
              VALUES 
              ('$exam_uid', '$student_id', '$school_uid', '$marks_obtained', '$grade', '$remarks', '$teacher_id')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

function updateExamResult($conn, $school_uid, $teacher_id) {
    $result_id = mysqli_real_escape_string($conn, $_POST['result_id']);
    $marks_obtained = mysqli_real_escape_string($conn, $_POST['marks_obtained']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    // Get exam details and verify teacher permission
    $verify_query = mysqli_query($conn, "SELECT er.result_id, es.max_marks 
                                       FROM exam_results er
                                       JOIN exam_schedules es ON er.exam_uid = es.exam_uid
                                       WHERE er.result_id = '$result_id' 
                                       AND es.teacher_id = '$teacher_id' 
                                       AND er.school_uid = '$school_uid'");
    
    if (mysqli_num_rows($verify_query) == 0) {
        echo "You don't have permission to update this result.";
        return;
    }
    
    $result_data = mysqli_fetch_array($verify_query);
    $max_marks = $result_data['max_marks'];
    
    // Validate marks
    if (!is_numeric($marks_obtained) || $marks_obtained < 0 || $marks_obtained > $max_marks) {
        echo "Marks must be between 0 and $max_marks.";
        return;
    }
    
    // Calculate grade
    $percentage = ($marks_obtained / $max_marks) * 100;
    $grade = calculateGrade($percentage);
    
    $query = "UPDATE exam_results SET 
              marks_obtained = '$marks_obtained',
              grade = '$grade',
              remarks = '$remarks'
              WHERE result_id = '$result_id'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo "success";
    } else {
        echo "Something went wrong. Please try again.";
    }
}

function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C+';
    if ($percentage >= 40) return 'C';
    if ($percentage >= 30) return 'D+';
    if ($percentage >= 20) return 'D';
    return 'F';
}
?>
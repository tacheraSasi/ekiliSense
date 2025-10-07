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

if ($formType == "single") {
    $student_id = $_POST["student"];
    $class_id = $_POST["class_id"];
    markPresent($conn, $student_id, $school_uid, $class_id);
} elseif ($formType == "all") {
    markAll($conn);
}

function markPresent(
    $conn,
    $student_id,
    $school_uid,
    $class_id,
    $is_all = false
) {
    // Validate student exists in the school
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ? AND school_uid = ?");
    $stmt->bind_param("ss", $student_id, $school_uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        if (!$is_all) {
            echo "Student not found";
        }
        $stmt->close();
        return;
    }
    $stmt->close();
    
    $now = date("Y-m-d");
    
    // Check if attendance already marked today
    $stmt = $conn->prepare("SELECT id FROM student_attendance WHERE student_id = ? AND attendance_date = ?");
    $stmt->bind_param("ss", $student_id, $now);
    $stmt->execute();
    $check_result = $stmt->get_result();
    $stmt->close();

    if ($check_result->num_rows == 0) {
        // Insert new attendance record
        $stmt = $conn->prepare("INSERT INTO student_attendance (student_id, school_uid, class_id, attendance_date, status) 
                                VALUES (?, ?, ?, ?, 1) 
                                ON DUPLICATE KEY UPDATE status = 1");
        $stmt->bind_param("ssss", $student_id, $school_uid, $class_id, $now);
        
        if ($stmt->execute()) {
            if (!$is_all) {
                echo "success";
            }
        } else {
            if (!$is_all) {
                echo "Something went wrong";
            }
        }
        $stmt->close();
    } else {
        if (!$is_all) {
            echo "success";
        }
    }
}

function markAll($conn)
{
    $class_id = mysqli_real_escape_string($conn, $_POST["class"]);
    $school_uid = $_SESSION["School_uid"];

    // Use prepared statement for security
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE school_uid = ? AND class_id = ?");
    $stmt->bind_param("ss", $school_uid, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "No students found in this class";
        $stmt->close();
        return;
    }

    $marked_count = 0;
    while ($student = $result->fetch_assoc()) {
        $std = $student["student_id"];
        markPresent($conn, $std, $school_uid, $class_id, true);
        $marked_count++;
    }
    $stmt->close();
    
    echo "success";
}

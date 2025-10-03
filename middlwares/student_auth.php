<?php 
if(!(isset($_SESSION['School_uid']) && isset($_SESSION['student_email']))){
    header("location:https://auth.ekilie.com/sense/student");
    exit();
}
$school_uid = $_SESSION['School_uid'];  
$student_email = $_SESSION['student_email'];

// Getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

// Getting the student details
$get_student = mysqli_query($conn, "SELECT * FROM students WHERE school_uid = '$school_uid' AND student_email = '$student_email'");

if (mysqli_num_rows($get_student) == 0) {
    session_destroy();
    header("location:https://auth.ekilie.com/sense/student");
    exit();
}

$student = mysqli_fetch_array($get_student);
$student_id = $student['student_id'];
$student_name = $student['student_name'];
$class_id = $student['class_id'];

// Getting the class info
$class_info = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM classes WHERE Class_id = '$class_id' AND school_unique_id = '$school_uid'"));

// Getting subjects for this class
$subjects = mysqli_query($conn,"SELECT * FROM subjects WHERE class_id = '$class_id'");
$subject_num = mysqli_num_rows($subjects);
?>

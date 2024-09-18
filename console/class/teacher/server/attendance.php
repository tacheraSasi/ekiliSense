<?php
session_start();
include_once "../../../../config.php";
if(!(isset($_SESSION['School_uid']) && isset($_SESSION['teacher_email']))){
  echo "Access denied";
  exit;
}
$formType = $_POST['form-type'];
$school_uid = $_SESSION['School_uid'];  
$teacher_email = $_SESSION['teacher_email'];

if($formType == "single"){
    $student_id = $_POST['student'];
    markPresent($conn,$student_id);
}else if($formType == "all"){
    markAll($conn);
}

function markPresent($conn,$student_id,$is_all = false){
    $now = date('Y-m-d');
    $check = mysqli_query($conn,"select * from student_attendance where 
    student_id = '$student_id' and attendance_date = '$now'");
    if(mysqli_num_rows($check) == 0){
        $query = mysqli_query($conn,"INSERT INTO student_attendance (student_id,status) 
        VALUES ('$student_id',1) ON DUPLICATE KEY UPDATE status= 1");

        if($query){
            if(!$is_all){
                echo "success";
            }
        }else{
            echo "Something went wrong";
            echo mysqli_error($conn);
        }
    }else{
        if(!$is_all){
            echo "success";
        }#duplicate data issues resolved, i will work on the logic 
    }
}

function markAll($conn){
    $class_id = $_POST["class"];
    $school_uid = $_SESSION["School_uid"];

    $get_students = mysqli_query($conn,"SELECT * FROM students 
    WHERE school_uid = '$school_uid' AND class_id = '$class_id'");

    while($student = mysqli_fetch_array($get_students)){
        $std = $student["student_id"];
        markPresent($conn,$std,true);
    }
    #then
    echo "success";


}
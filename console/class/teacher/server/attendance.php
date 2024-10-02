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
    $class_id = $_POST['class_id'];
    markPresent($conn,$student_id,$school_uid,$class_id);
}else if($formType == "all"){
    markAll($conn);
}

function markPresent($conn,$student_id,$school_uid,$class_id,$is_all = false){
    #getting the students info
    $get_std_info = mysqli_query($conn, "select * from students where student_id = '$student_id' and school_uid = '$school_uid'");
    $now = date('Y-m-d');# for later usage, by dafault attendace_date takes the current timestamp


    $check = mysqli_query($conn,"select * from student_attendance where 
    student_id = '$student_id' and attendance_date = '$now'");

    if(mysqli_num_rows($check) == 0){
        $query = mysqli_query($conn,"INSERT INTO student_attendance (student_id,school_uid,class_id,attendance_date,status) 
        VALUES ('$student_id','$school_uid','$class_id','$now',1) ON DUPLICATE KEY UPDATE status= 1");

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
        markPresent($conn,$std,$school_uid,$class_id,true);
    }
    #then
    echo "success";

}
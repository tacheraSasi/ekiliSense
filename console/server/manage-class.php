<?php 
session_start();
include_once "../../config.php";
$school_uid = $_SESSION['School_uid'];
if(!isset($school_uid)){
    echo "ACCESS DENIED";
    exit;
}
if(isset($_POST['save-changes'])){
    $refferer = $_SERVER['HTTP_REFERER'];# getting the url for the reffer/the teacher's page
    if(editClass($conn,$school_uid,$_POST['class'])){
        // var_dump($_POST);
        header("location:$refferer");# redirect back to the teacher's view page with the new changes
    }
}

function editClass($conn,$school_uid,$class_id){
    $name = mysqli_real_escape_string($conn,$_POST['className']);
    $class_teacher = mysqli_real_escape_string($conn,$_POST['choosen-class-teacher']);
    // var_dump($_POST);
  
    $queryClassName = "UPDATE `classes` SET `Class_name` = '$name' WHERE 
    `classes`.`Class_id` = '$class_id' AND `classes`.`school_unique_id` = '$school_uid'";
    
    $queryClassTeacher = "UPDATE `class_teacher` SET `teacher_id` = '$class_teacher' WHERE 
    `class_teacher`.`Class_id` = '$class_id' AND `class_teacher`.`school_unique_id` = '$school_uid'";
    
    
    $updateClassName = mysqli_query($conn,$queryClassName);
    $updateClassTeacher = mysqli_query($conn,$queryClassTeacher);
    $isUpdated = false;
    if($updateClassName && $updateClassTeacher){
      return true;
    }
}
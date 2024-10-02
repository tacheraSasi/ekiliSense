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
    if(editTeacher($conn,$school_uid,$_POST['teacher'])){
        // var_dump($_POST);
        header("location:$refferer");# redirect back to the teacher's view page with the new changes
    }
}
  
#function to edit teacher
function editTeacher($conn,$school_uid,$teacher_id){
    $name = mysqli_real_escape_string($conn,$_POST['fullname']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);
  
    $query = "UPDATE `teachers` SET `teacher_fullname` = '$name',`teacher_email` = '$email', 
    `teacher_active_phone` = '$phone', `teacher_home_address` = '$address' WHERE 
    `teachers`.`teacher_id` = '$teacher_id' AND `teachers`.`School_unique_id` = '$school_uid'";
    #TODO:Check if verified
    
    $update = mysqli_query($conn,$query);

    if($update){
        return true;
    }else{
        return false;
    }
    
  
}
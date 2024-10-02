<?php
session_start();
include_once "../../../../config.php";
if(!(isset($_SESSION['School_uid']) && isset($_SESSION['teacher_email']))){
  echo "Access denied";
  exit;
}

if(isset($_POST['edit-plan'])){
    $refferer = $_SERVER['HTTP_REFERER'];
    if(editPlan($conn)){
        header("location:$refferer");
    }
};


function editPlan($conn){
    $title = $_POST["plan-title"];
    $progress = $_POST["progress"];
    $desc = $_POST["plan-desc"];
    $uid = $_POST["uid"];
    $owner = $_POST["owner"];
    $edit = mysqli_query($conn,
    "UPDATE `plans` SET `title` = '$title ', `description` = '$desc' , 
    `progress` = '$progress' WHERE `plans`.`uid` = '$uid' AND `plans`.`owner` = '$owner'");

    if($edit){
        return true;
    }else{
        false;
    }
}
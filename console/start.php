<?php
session_start();
include_once "../config.php";
echo isset($_GET['token']);

if(isset($_GET['token']) && isset($_GET['u']) && isset($_GET['s']) && isset($_GET['redirect_to'])){
    $user = $_GET['u'];
    $token = $_GET['token'];
    $to = $_GET['redirect_to'];
    #teacher_uid
    $q = mysqli_fetch_array(mysqli_query($conn,"SELECT teacher_id FROM teachers WHERE teacher_email = '$user'" ));
    
    #checking if the params are valid
    $check = mysqli_query($conn,"SELECT * FROM verify_login WHERE user = '$user' AND otp = '$token'");
    $values = mysqli_fetch_array($check);
    if($values['otp'] == $token){
        
        $_SESSION['School_uid'] = $_GET['s'];
        $_SESSION['teacher_email'] = $user;

        #deleting the token
        mysqli_query($conn,"DELETE FROM `verify_login` WHERE `verify_login`.`user` = '$user'");
        header("location:./$to");
    }else{
        echo"sql failed";
        header("location:404/?issue=sql");
    }

}else{
    header("location:404/?issue=params");
}
<?php
session_start();
include_once "../../config.php";

$instituteName = mysqli_real_escape_string($conn, $_POST['institute-name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = 0;
$country = null;
$password = mysqli_real_escape_string($conn, $_POST['password']);
$confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm-password']);

if (!empty($instituteName) && !empty($email)  && !empty($password)) {

    $sql = mysqli_query($conn, "SELECT * FROM schools WHERE school_email = '{$email}' 
           OR school_name = '{$instituteName}'");
    if (mysqli_num_rows($sql) > 0) {
        echo "institute's name or email is unvailable or already taken.";
    } else {

        $ran_id = rand(time(), 700000000);
        $status = "Active now";
        if($password == $confirmPassword){
            $encrypt_pass = md5($password);
            $insert_query = mysqli_query($conn, 
            "INSERT INTO schools (unique_id, school_name, school_phone, school_email, auth, country)
            VALUES ({$ran_id}, '{$instituteName}','{$phone}', '{$email}', '{$encrypt_pass}','{$country}')");
            
            $check = mysqli_query($conn, 
            "SELECT * FROM schools WHERE school_email = '{$email}' AND school_name = '{$instituteName}'");
            if(mysqli_num_rows($check)>0){
                $result = mysqli_fetch_assoc($check);
                $_SESSION['School_uid'] = $result['unique_id'];
                if(isset($_SESSION['School_uid'])){
                    echo'success';
                }else{
                    echo 'something went wrong. Please try again later';
                }
            }else{
                echo'something went wrong. Please try again later.';
            }
        } else{
            echo'password does not match!';
        } 
    }
} else {
    echo "All input fields are required!";
}


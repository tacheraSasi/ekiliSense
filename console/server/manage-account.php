<?php 
session_start();
include_once "../../config.php";
$school_uid = $_SESSION['School_uid'];
if(!isset($school_uid)){
    echo "ACCESS DENIED";
    exit;
};

if(isset($_POST['save-changes'])){
    $refferer = $_SERVER['HTTP_REFERER'];# getting the url for the reffer/the teacher's page
    if(editSchool($conn,$school_uid)){
        // var_dump($_POST);
        header("location:$refferer");# redirect back to the teacher's view page with the new changes
    }
}elseif(isset($_POST['change-password'])){
    $refferer = $_SERVER['HTTP_REFERER'];# getting the url for the reffer/the teacher's page
    if(changePassword($conn,$school_uid)){
        // var_dump($_POST);
        header("location:$refferer");# redirect back to the teacher's view page with the new changes
    }
    // else{
    //     header("location:$refferer");# redirect back to the teacher's view page with the new changes

    // }
}

function editSchool($conn,$school_uid){
    $schoolName = $_POST['schoolName'];
    $about = mysqli_real_escape_string($conn,$_POST['about']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);

    $query = "UPDATE `schools` SET `School_name` = '$schoolName',`School_email` = '$email',
    `School_phone` = '$phone', `about` = '$about' WHERE `schools`.`unique_id` = '$school_uid'; ";

    #running the query
    if(mysqli_query($conn,$query)){
        return true;
    }#TODO:figure out a logic to handle errors 


}

function changePassword($conn,$school_uid){
    $oldPassword = $_POST['password'];
    $newPassword = $_POST['newpassword'];
    $cPassword = $_POST['cpassword'];

    $sql = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '{$school_uid}' ");
    
    #checking if the old password entered by the admin is correct
    if(mysqli_num_rows($sql) > 0){
        $row = mysqli_fetch_assoc($sql);
        $user_pass = md5($oldPassword);
        $enc_pass = $row['auth'];

        if($user_pass != $enc_pass){
            $_SESSION['error'] = [
                "message"=>"Incorrect old password",
                "source"=>"manage-account"
            ];
            return true;# Returned true so that the admin will be redirected to the refferer
            #I will fix the logic here 
                
        }
    } 

    #checking if new password and confirm password matches
    if($newPassword != $cPassword){
        $_SESSION['error'] = [
            "message"=>"Password does not match",
            "source"=>"manage-account"
        ];
        return true;# Returned true so that the admin will be redirected to the refferer
        #I will fix the logic here 

    }
    $newEncPass = md5($newPassword);# change this to something better later 
    $query = "UPDATE `schools` SET `auth` = '$newEncPass' WHERE `schools`.`unique_id` = '$school_uid'; ";
    $q = mysqli_query($conn,$query);

    if($q){
        $_SESSION['error'] = null;
        return true;
    }

}
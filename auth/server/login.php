<?php 
session_start();
include_once "../../onboarding/server/config.php";

// Checking if the login attempts counter is set in the session, initialize it to 0 if not.
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

if ($_SESSION['login_attempts'] < 3) {
    
    if(!empty($email) && !empty($password)){
        $sql = mysqli_query($conn, "SELECT * FROM schools WHERE school_email = '{$email}' ");
        
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
            $user_pass = md5($password);
            $enc_pass = $row['auth'];
            
            if($user_pass === $enc_pass){
            // Reseting login attempts on successful login
                $_SESSION['login_attempts'] = 0;

                $_SESSION['School_uid'] = $row['unique_id'];
                if(isset($_SESSION['School_uid'])){
                    echo "success";

                }else{
                    echo'Something went wrong. Please try again';
                }
                
            } else {
                // Incrementing login attempts on failed login
                $_SESSION['login_attempts']++;

                // Checking if the maximum number of login attempts is reached
                if ($_SESSION['login_attempts'] >= 3) {
                    
                    echo "Maximum login attempts reached. Please try again later.";
                } else {
                    echo "Invalid password. Attempts left: " . (3 - $_SESSION['login_attempts']);
                }
            }
        } else {
            echo "Something went wrong! Please Try again";
        }
    } else {
        echo "All input fields are required!";
    }
}else{
    echo'Too many attempts! Please try again later';
}


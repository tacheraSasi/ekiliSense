<?php
session_start();
include_once "../../config.php";
$school_uid = $_SESSION['School_uid'];

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);
$school_name = $school['School_name'];

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($school_uid)){
    if(isset($_POST['recipients'])){
        $recipients = $_POST['recipients'];
        $title = $_POST['title'];
        $message = $_POST['message'];
        foreach ($recipients as $recipient) {
            if($recipient == "teachers"){
                #sending to Teachers
                sendToTeachers($school_uid,$school_name,$conn,$title,$message);

            }else if($recipient == "parents"){
                #sending to Parents
                sendToParents($school_uid,$school_name,$conn,$title,$message);

            }else if($recipient == "classTeachers"){
                #sending to ClassTeachers
                sendToClassTeachers($school_uid,$school_name,$conn,$title,$message);
                
            }
        }
        echo "success";

    }else{
        echo "No recipient was selected 💀";
    }

}
function sendToTeachers($school_uid,$school_name,$conn,$title,$message){
    $get_teachers = mysqli_query($conn,"SELECT * FROM teachers WHERE School_unique_id = '$school_uid'");
    while($teachers = mysqli_fetch_array($get_teachers)){
        $email = $teachers['teacher_email'];
        $name = $teachers['teacher_fullname'];
        if(isset($email)){
            sendMail($email,$name,$school_name,$title,$message);

        }else{
            continue;
        }
    }


}

function sendToParents($school_uid,$school_name,$conn,$title,$message){
    $get_students = mysqli_query($conn,"SELECT * FROM students WHERE school_uid = '$school_uid'");
    while($students = mysqli_fetch_array($get_students)){
        $email = $students["parent_email"];
        if(isset($email)){
            sendMail($email,"Parent/Gaurdian",$school_name,$title,$message);

        }else{
            continue;
        }
    }
    


}

function sendToClassTeachers($school_uid,$school_name,$conn,$title,$message){
    $get_class_teachers = mysqli_query($conn,"SELECT * FROM class_teacher WHERE school_unique_id = '$school_uid'");
    while($class_teachers = mysqli_fetch_array($get_class_teachers)){
        $teacher_uid = $class_teachers['teacher_id'];

        $teacher = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM teachers WHERE teacher_id = '$teacher_uid'"));
        
        $email = $teacher['teacher_email'];
        $name = $teacher['teacher_fullname'];
        if(isset($email)){
            sendMail($email,$name,$school_name,$title,$message);
        }else{
            continue;
        }
    }
}

#function that handles the sending of emails
function sendMail($email, $name,$school_name,$title,$message) {
    # Setting email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $school_name  <support@ekilie.com>";#change this email later on to no reply
    
    # Email subject
    $subject = "$school_name | ekiliSense";
    
    #Email content
    $content = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="max-width: 600px; margin: 0 auto; border-radius: 1rem; padding: 1rem; color: #fff; background-color: rgba(35, 51, 29, 0.77);">
        <div style="text-align: center;">
            <h1 style="color: #a6ec6d;">'.$title.' | ekiliSense</h1>
        </div>
        <div style="margin-top: 20px; padding: 20px; background-color: rgba(255, 255, 255, 0.45); border-radius: 10px;">
            <p>Dear ' . $name . ',</p> <br>
           
            <pre>'.$message.'</pre>
        </div>
            
        <div style="margin-top: 20px; font-style: italic; text-align: center; color: #a6ec6d;">
            <p>Best regards,</p>
            <p>'.$school_name.' @ekiliSense</p>
            <p>The ekiliSense Team</p>
        </div>
    </body>
    </html>
    ';
    
    # Sending the email
    mail($email, $subject, $content, $headers);
    
}
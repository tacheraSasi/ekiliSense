<?php
// Headers



session_start();
include_once "../../config.php";
include_once "../functions/google.php";
require_once "../../app/app.php";

$formType = $_POST['form-type'];
$school_uid = $_SESSION['School_uid']; 
if(!isset($school_uid)){
    echo "ACCESS DENIED";
    exit;
}

// connected echo ""; #for debugging purposes

#getting google data
// $get_google_data = mysqli_query($conn,"SELECT * FROM teachers_google WHERE email = '$teacher_email'");
// $isConnectedToGoogle = false;

// if (mysqli_num_rows($get_google_data)>0){
//   $isConnectedToGoogle = true; #teacher has connected his account to google
//   $google_data = mysqli_fetch_assoc($get_google_data);
// }

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);
$school_name = $school['School_name'];

switch($formType) {
    case 'teacher':
        addTeacher($conn, $school_uid, $school_name);
        break;
    case 'class':
        addClass($conn, $school_uid);
        break;
    case 'class-teacher':
        addClassTeacher($conn, $school_uid);
        break;
    case 'student':
        addStudent($conn, $school_uid);
        break;
    case 'subject':
        addSubject($conn, $school_uid);
        break;
    case 'plan':
        addPlan($conn,$school_uid,$isConnectedToGoogle);
        break;
    case 'stuff-attendance':
        addStuffAttendance($conn,$school_uid);
        break;
    default:
        echo "Invalid form type!";
        break;
}

#function to add plan
function addPlan($conn,$school_uid,$isConnectedToGoogle){
    $title = mysqli_real_escape_string($conn,$_POST['plan-title']);
    $desc = mysqli_real_escape_string($conn,$_POST['plan-desc']);
    $progress = mysqli_real_escape_string($conn,$_POST['progress']);
    $owner = mysqli_real_escape_string($conn,$_POST['owner']);
    $uid = planUID();

    $insert_plan = mysqli_query($conn,"INSERT INTO plans
    (school_uid,uid,owner,title,description,progress)
    VALUE ('$school_uid','$uid','$owner','$title','$desc','$progress')");

    if($insert_plan){
        echo "success";

        // Syncing the plan with Google Calendar
        if (!$isConnectedToGoogle) return;
        syncPlanWithGoogleCalendar($conn, $owner, $title, $desc);
    
    }else{
        echo "Something Went wrong,Please try again";
    }


}

function addStuffAttendance($conn,$school_uid){
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $owner = mysqli_real_escape_string($conn, $_POST['owner']);
    
    // Validate geolocation data
    if (empty($latitude) || empty($longitude)) {
        echo "Geolocation data is required";
        return;
    }
    
    // Get teacher ID
    $teacher_query = mysqli_query($conn, "SELECT teacher_id FROM teachers 
                                          WHERE teacher_email = '$owner' 
                                          AND School_unique_id = '$school_uid'");
    
    if (mysqli_num_rows($teacher_query) == 0) {
        echo "Teacher not found";
        return;
    }
    
    $teacher = mysqli_fetch_array($teacher_query);
    $teacher_id = $teacher['teacher_id'];
    $now = date("Y-m-d");
    
    // Check if attendance already marked today
    $check_query = mysqli_query($conn, "SELECT * FROM staff_attendance 
                                        WHERE teacher_id = '$teacher_id' 
                                        AND attendance_date = '$now' 
                                        AND school_uid = '$school_uid'");
    
    if (mysqli_num_rows($check_query) > 0) {
        echo "Attendance already marked for today";
        return;
    }
    
    // Insert attendance record
    $insert_query = mysqli_query($conn, "INSERT INTO staff_attendance 
                                         (school_uid, teacher_id, attendance_date, status, latitude, longitude) 
                                         VALUES ('$school_uid', '$teacher_id', '$now', 1, '$latitude', '$longitude')");
    
    if ($insert_query) {
        echo "success";
    } else {
        echo "Failed to mark attendance. Please try again.";
    }
}

function planUID() {
    # Generating 8 random characters (letters and numbers)
    $randomString = bin2hex(random_bytes(4)); # 4 bytes = 8 characters in hex
    return 'plan-'. $_SESSION['School_uid'] .'-'. $randomString;
}

#function to add student
function addStudent($conn, $school_uid){
    $fname = mysqli_real_escape_string($conn,$_POST['fname']);
    $lname = mysqli_real_escape_string($conn,$_POST['lname']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $mobile = mysqli_real_escape_string($conn,$_POST['mobile']);
    $class = mysqli_real_escape_string($conn,$_POST['class']);
    $student_id = UID();

    #TODO:Some logic to check if the student already exists

    $insert_student = mysqli_query($conn,"INSERT INTO students 
    (school_uid, student_id,student_first_name,student_last_name,class_id,parent_phone,parent_email) 
    VALUES ('$school_uid','$student_id','$fname','$lname','$class','$mobile','$email')");

    if($insert_student){
        echo "success";

    }else{
        echo "Something Went wrong,Please try again";
    }



    
}

#function to add subject
function addSubject($conn, $school_uid){
    $teacher = mysqli_real_escape_string($conn,$_POST["choosen-subject-teacher"]); #TODO:remove the id from the frontend
    $class = mysqli_real_escape_string($conn,$_POST['class']);
    $subjectName = mysqli_real_escape_string($conn,$_POST['subject-name']);
    
    $subject_uid = randString(15);
    #TODO: add in aloop to check if the uid already exists in the database

    $insert_subject = mysqli_query($conn,"INSERT INTO subjects (subject_uid,subject_name,School_unique_id,class_id,teacher_id)
    VALUE ('$subject_uid','$subjectName','$school_uid','$class','$teacher')");

    if($insert_subject){
        echo "success";
    }else{
        echo "Something Went wrong,Please try again";
    }
}

#function to add teachers
function addTeacher($conn, $school_uid, $school_name) {
    # Sanitize and validate input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);

    # Check if the teacher already exists
    $check_query = mysqli_query($conn, "SELECT * FROM teachers 
        WHERE teacher_email = '$email' AND School_unique_id = '$school_uid'");

    if (mysqli_num_rows($check_query) > 0) {
        echo "Teacher already exists";
    } else {
        # Generate unique teacher ID
        $teacher_uid = UID();
        
        # Insert new teacher into the database
        $insert_teacher_query = mysqli_query($conn, "INSERT INTO teachers 
            (school_unique_id, teacher_id, teacher_fullname, teacher_email, teacher_active_phone) 
            VALUES ('$school_uid', '$teacher_uid', '$name', '$email', '$mobile')");
        
        if ($insert_teacher_query) {
            # Generate OTP
            $otp = randString(10);
            
            # Insert OTP into the database
            $insert_otp_query = mysqli_query($conn, "INSERT INTO otps 
                (teacher_email, school_id, value) VALUES ('$email', '$school_uid', '$otp')");
            
            if ($insert_otp_query) {
                # Send verification email
                sendMail($email, $name, $otp, $school_name);
            } else {
                echo "Failed to generate OTP. Please try again.";
            }
        } else {
            echo "Something went wrong. Please try again.";
        }
    }
}


#function to add classes
function addClass($conn, $school_uid){
    $class_name = mysqli_real_escape_string($conn, $_POST['class-name']);
    $check = mysqli_query($conn, "SELECT * FROM classes 
    WHERE Class_name = '$class_name' AND school_unique_id = '$school_uid'");

    if(!$check){
        echo"class already exists";
    }else{
        $query = mysqli_query($conn,"INSERT INTO classes (school_unique_id, Class_name) 
        VALUES ('$school_uid','$class_name')");
        if($query){
            echo"success";

        }else{
            echo"something went wrong, Try again.";
        }
    }
}

function addClassTeacher($conn, $school_uid) {
    $class = mysqli_real_escape_string($conn, $_POST['choosen-class']);
    $teacher = mysqli_real_escape_string($conn, $_POST['choosen-class-teacher']);
    
    if($class == "Choose a teacher" || $teacher == "Select a class"){
        echo "Please choose before submitting";
        exit;
    }

    # Getting the class id using the class name
    $get_class_id_query = mysqli_query($conn, 
        "SELECT Class_id FROM classes WHERE Class_name = '$class' AND School_unique_id = '$school_uid'");
    $get_class_id = mysqli_fetch_assoc($get_class_id_query);
    if (!$get_class_id) {
        echo "Class not found.";
        exit;
    }
    $class_id = $get_class_id['Class_id'];

    # Getting the teacher's id using the teacher's name
    $get_teacher_id_query = mysqli_query($conn, 
        "SELECT teacher_id FROM teachers WHERE teacher_fullname = '$teacher' AND School_unique_id = '$school_uid'");
    $get_teacher_id = mysqli_fetch_assoc($get_teacher_id_query);
    if (!$get_teacher_id) {
        echo "Teacher not found.";
        exit;
    }
    $teacher_id = $get_teacher_id['teacher_id'];

    # Check if the class already has a teacher assigned
    $check_query = mysqli_query($conn, "SELECT * FROM class_teacher 
        WHERE Class_id = '$class_id' AND School_unique_id = '$school_uid'");
    $check = mysqli_fetch_assoc($check_query);

    if ($check) {
        echo "Class already has a class teacher. To change, open the class's page.";
    } else {
        $insert_query = mysqli_query($conn, "INSERT INTO class_teacher (school_unique_id, Class_id, teacher_id) 
            VALUES ('$school_uid', '$class_id', '$teacher_id')");
        if ($insert_query) {
            echo "success";
            # TODO: add logic for notifying the teacher about the position
        } else {
            echo "Something went wrong. Try again.";
        }
    }
}

#function to generate the otp
function randString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    $max = strlen($characters) - 1;
    
    # Generating random characters from the character set
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, $max)];
    }
    
    return $string;
}
#function to generate teachers uid
function UID(){
    # $characters = 'abcdefghijklmnopqrstuvwxyz';
    $ran_id = rand(time(), 10000);
    $uid ="$ran_id";
    # $uid .= "_";
    # $max = strlen($characters) - 1;
    
    # # Generate random characters from the character set
    # for ($i = 0; $i < 4 ; $i++) {
    #     $uid .= $characters[rand(0, $max)];
    # }
    
    return $uid;

}

function sendMail($email, $name, $otp,$school_name) {
    # Setting email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $school_name  <support@ekilie.com>";#TODO:change this email later on to no reply
    
    # Email subject
    $subject = "$school_name verification | ekiliSense";
    
    #Email content
    $message = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="max-width: 600px; margin: 0 auto; border-radius: 1rem; padding: 1rem; color: #fff; background-color: rgba(35, 51, 29, 0.77);">
        <div style="text-align: center;">
            <h1 style="color: #a6ec6d;">Activate Your Account | ekiliSense</h1>
        </div>
        <div style="margin-top: 20px; padding: 20px; background-color: rgba(255, 255, 255, 0.45); border-radius: 10px;">
            <p>Dear ' . $name . ',</p>
            <p style="color: #eee;">
                Congratulations! You have been successfully registered as a teacher at <strong>'.$school_name.'</strong>.
            </p>
            <p style="color: #eee;">
                To start exploring our platform and accessing your account, please click the button below to verify your email address:
            </p>
            <div style="text-align: center; margin-top: 20px;">
                <a href="https://auth.ekilie.com/sense/verify/?otp='.$otp.'&user='.$email.'" style="background-color: #8cc75c; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Verify Account</a>
            </div>
            <p style="color: #eee; margin-top: 20px;">
                Once your account is verified, you will unlock full access to our platform and resources, empowering you to elevate your teaching experience at <b> '.$school_name.'</b>. You will be able to:
            </p>
            <ul style="color: #eee;">
                <li>Access and create interactive lesson plans</li>
                <li>Engage with students through live sessions and discussions</li>
                <li>Utilize our extensive library of educational resources</li>
                <li>Track student progress and provide personalized feedback</li>
            </ul>
            <p style="color: #eee;">
                Should you require any assistance or have questions, please do not hesitate to reach out to our dedicated support team at <a href="mailto:support@ekilie.com" style="color: #a6ec6d; text-decoration: none;">support@ekilie.com</a>.
            </p>
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
    if(mail($email, $subject, $message, $headers)) {
        echo "success";
    } else {
        echo "Teacher added but failed to send email";
    }
}

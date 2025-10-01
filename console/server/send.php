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
        $notificationType = isset($_POST['notificationType']) ? $_POST['notificationType'] : 'email';
        
        foreach ($recipients as $recipient) {
            if($recipient == "teachers"){
                #sending to Teachers
                sendToTeachers($school_uid,$school_name,$conn,$title,$message,$notificationType);

            }else if($recipient == "parents"){
                #sending to Parents
                sendToParents($school_uid,$school_name,$conn,$title,$message,$notificationType);

            }else if($recipient == "classTeachers"){
                #sending to ClassTeachers
                sendToClassTeachers($school_uid,$school_name,$conn,$title,$message,$notificationType);
                
            }
        }
        echo "success";

    }else{
        echo "No recipient was selected 💀";
    }

}
function sendToTeachers($school_uid,$school_name,$conn,$title,$message,$notificationType = 'email'){
    $get_teachers = mysqli_query($conn,"SELECT * FROM teachers WHERE School_unique_id = '$school_uid'");
    while($teachers = mysqli_fetch_array($get_teachers)){
        $email = $teachers['teacher_email'];
        $phone = $teachers['teacher_active_phone'];
        $name = $teachers['teacher_fullname'];
        
        if($notificationType == 'email' || $notificationType == 'both'){
            if(isset($email)){
                sendMail($email,$name,$school_name,$title,$message);
            }
        }
        
        if($notificationType == 'sms' || $notificationType == 'both'){
            if(isset($phone) && !empty($phone)){
                sendSMS($phone,$name,$school_name,$title,$message);
            }
        }
    }

}

function sendToParents($school_uid,$school_name,$conn,$title,$message,$notificationType = 'email'){
    $get_students = mysqli_query($conn,"SELECT * FROM students WHERE school_uid = '$school_uid'");
    while($students = mysqli_fetch_array($get_students)){
        $email = $students["parent_email"];
        $phone = $students["parent_phone"];
        
        if($notificationType == 'email' || $notificationType == 'both'){
            if(isset($email) && !empty($email)){
                sendMail($email,"Parent/Guardian",$school_name,$title,$message);
            }
        }
        
        if($notificationType == 'sms' || $notificationType == 'both'){
            if(isset($phone) && !empty($phone)){
                sendSMS($phone,"Parent/Guardian",$school_name,$title,$message);
            }
        }
    }
    


}

function sendToClassTeachers($school_uid,$school_name,$conn,$title,$message,$notificationType = 'email'){
    $get_class_teachers = mysqli_query($conn,"SELECT * FROM class_teacher WHERE school_unique_id = '$school_uid'");
    while($class_teachers = mysqli_fetch_array($get_class_teachers)){
        $teacher_uid = $class_teachers['teacher_id'];

        $teacher = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM teachers WHERE teacher_id = '$teacher_uid'"));
        
        $email = $teacher['teacher_email'];
        $phone = $teacher['teacher_active_phone'];
        $name = $teacher['teacher_fullname'];
        
        if($notificationType == 'email' || $notificationType == 'both'){
            if(isset($email) && !empty($email)){
                sendMail($email,$name,$school_name,$title,$message);
            }
        }
        
        if($notificationType == 'sms' || $notificationType == 'both'){
            if(isset($phone) && !empty($phone)){
                sendSMS($phone,$name,$school_name,$title,$message);
            }
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
           
            <pre style="font-family:inherit;">'.$message.'</pre>
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

#function that handles the sending of SMS
function sendSMS($phone, $name, $school_name, $title, $message) {
    # Load environment variables
    include_once "../../parseEnv.php";
    
    # Get SMS API configuration from environment
    $baseUrl = getenv('NOTIFY_AFRICA_API_URL') ?: 'https://example.com/v2';
    $apiToken = getenv('NOTIFY_AFRICA_API_TOKEN') ?: '';
    $senderId = getenv('NOTIFY_AFRICA_SENDER_ID') ?: 1;
    
    # Skip if no API token is configured
    if(empty($apiToken) || $apiToken == 'your_api_token_here'){
        error_log("SMS API token not configured. Skipping SMS for: " . $phone);
        return false;
    }
    
    # Format the phone number (remove non-numeric characters)
    $formattedPhone = preg_replace('/[^0-9]/', '', $phone);
    
    # Skip if phone number is empty or invalid
    if(empty($formattedPhone)){
        error_log("Invalid phone number format: " . $phone);
        return false;
    }
    
    # Prepare SMS content
    $smsText = "$title\n\nDear $name,\n\n$message\n\nBest regards,\n$school_name @ekiliSense";
    
    # Truncate message if too long (most SMS APIs have a limit)
    if(strlen($smsText) > 160){
        $smsText = substr($smsText, 0, 157) . '...';
    }
    
    # API endpoint
    $url = "$baseUrl/send-sms";
    
    # Prepare payload
    $payload = array(
        'sender_id' => $senderId,
        'schedule' => 'none',
        'sms' => $smsText,
        'recipients' => array(
            array('number' => $formattedPhone)
        )
    );
    
    # Set the headers and request options
    $options = array(
        'http' => array(
            'header' => "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n" .
                        "Authorization: Bearer $apiToken\r\n",
            'method' => 'POST',
            'content' => json_encode($payload),
            'ignore_errors' => true
        )
    );
    
    # Create stream context
    $context = stream_context_create($options);
    
    # Send the SMS
    try {
        $result = @file_get_contents($url, false, $context);
        
        # Log the response for debugging
        if ($result === false) {
            error_log("Failed to send SMS to: " . $phone);
            return false;
        }
        
        $response = json_decode($result, true);
        error_log("SMS sent to " . $phone . ": " . json_encode($response));
        return true;
        
    } catch (Exception $e) {
        error_log("SMS sending error: " . $e->getMessage());
        return false;
    }
}
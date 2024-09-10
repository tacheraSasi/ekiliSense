<?php
// require "vendor/autoload.php";
// use ekilie\EkiliRelay;

// $ekilirelay = new EkiliRelay("");
// $res = $ekilirelay->sendEmail("","","","");
// print_r($res);
$formType = "plan";
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
        echo" plan";
        break;
    default:
        echo "Invalid form type!";
        break;
}

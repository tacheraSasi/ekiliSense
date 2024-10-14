<?php 
if(!(isset($_SESSION['School_uid']) && isset($_SESSION['teacher_email']))){
    header("location:https://auth.ekilie.com/sense/teacher");
}
$school_uid = $_SESSION['School_uid'];  
#hard Coded value to be used in development 
// $_SESSION['teacher_email'] = "tacherasasi@gmail.com"; #hard-coded value will change 
$teacher_email = $_SESSION['teacher_email'];

#getting google data
$get_google_data = mysqli_query($conn,"SELECT * FROM teachers_google WHERE email = '$teacher_email'");
$isConnectedToGoogle = false;

if (mysqli_num_rows($get_google_data)>0){
  $isConnectedToGoogle = true; #teacher has connected his account to google
  $google_data = mysqli_fetch_assoc($get_google_data);
}

#getting the school details 
$get_info = mysqli_query($conn, "SELECT * FROM schools WHERE unique_id = '$school_uid'");
$school = mysqli_fetch_array($get_info);

#getting the class teachers details
$get_class_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_class_teacher);
$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['teacher_fullname'];
$subjects = mysqli_query($conn,"SELECT * FROM subjects WHERE teacher_id = '$teacher_id'");


$get_teachers = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid'");


#getting the class info
$get_class_id = mysqli_query($conn, "SELECT * FROM class_teacher WHERE school_unique_id = '$school_uid' AND teacher_id = '$teacher_id'");

if(mysqli_num_rows($get_class_id) == 0){
  header("location:../../teacher");
}else{
  $class_id = mysqli_fetch_array($get_class_id)['Class_id'];

}

$class_info = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM classes WHERE Class_id = '$class_id' AND school_unique_id = '$school_uid' "));

#getting students info
$students = mysqli_query($conn,"SELECT * FROM students WHERE class_id = '$class_id'  ORDER BY `students`.`student_first_name` ASC");
$student_num = mysqli_num_rows($students);

#getting subjects info
$subjects = mysqli_query($conn,"SELECT * FROM subjects WHERE class_id = '$class_id'");
$subject_num = mysqli_num_rows($subjects);

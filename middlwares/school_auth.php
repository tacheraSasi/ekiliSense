<?php
include_once "../app/api.php";
if(!isset($_SESSION['School_uid'])){
  header("location:../auth");
}


#if the user is a teacher 
if(isset($_SESSION['teacher_email'])){
  $t_email = $_SESSION['teacher_email'];
  $suid = $_SESSION['School_uid'];
  #getting the teacher's id using the teacher's name
  $get_teacher_id = mysqli_fetch_assoc(mysqli_query($conn, 
  "SELECT * FROM teachers WHERE teacher_email = '$t_email' AND School_unique_id = '$suid'"));
  $t_id = $get_teacher_id['teacher_id'];

  $check_is_class_teacher = mysqli_query($conn,
  "SELECT * FROM class_teacher WHERE school_unique_id = '$suid' AND teacher_id = '$t_id'");

  if(mysqli_num_rows($check_is_class_teacher) > 0){
    header("location:./class/teacher/");
  }else{
    header("location:./teacher/");
  }
}
$school_uid = $_SESSION['school_uid'];

#getting the school details 
$school = Api::getSchoolByUniqueId($school_uid);

#getting the list of classes
$get_classes = mysqli_query($conn, "SELECT * FROM classes WHERE school_unique_id = '$school_uid'");
$classes_count = mysqli_num_rows($get_classes);

#getting the list of teachers
$get_teachers = mysqli_query($conn, "SELECT * FROM teachers WHERE School_unique_id = '$school_uid'");
$teachers_count = mysqli_num_rows($get_teachers);

#getting the list of students
$get_students = mysqli_query($conn, "SELECT * FROM students WHERE school_uid = '$school_uid'");
$students_count = mysqli_num_rows($get_students);
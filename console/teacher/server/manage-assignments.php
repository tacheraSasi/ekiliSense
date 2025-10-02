<?php
session_start();
include_once "../../../config.php";

if(!isset($_SESSION['teacher_email']) || !isset($_SESSION['School_uid'])){
  header("location:https://auth.ekilie.com/sense/teacher");
  exit();
}

$teacher_email = $_SESSION['teacher_email'];
$school_uid = $_SESSION['School_uid'];

// Get teacher ID
$get_teacher = mysqli_query($conn, "SELECT teacher_id FROM teachers WHERE school_unique_id = '$school_uid' AND teacher_email = '$teacher_email'");
$teacher = mysqli_fetch_array($get_teacher);
$teacher_id = $teacher['teacher_id'];

// Create Assignment
if(isset($_POST['create_assignment'])){
  $title = mysqli_real_escape_string($conn, $_POST['title']);
  $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
  
  // Verify teacher owns this subject
  $verify_subject = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id = '$subject_id' AND teacher_id = '$teacher_id'");
  
  if(mysqli_num_rows($verify_subject) == 0){
    $_SESSION['error'] = "You don't have permission to create assignments for this subject.";
    header("location:../assignments.php");
    exit();
  }
  
  // Generate unique assignment ID
  $assignment_uid = uniqid('assignment_', true);
  
  // Insert assignment
  $insert = mysqli_query($conn, "INSERT INTO homework_assignments 
                                 (assignment_uid, assignment_title, assignment_description, subject_id, teacher_id, deadline, created_at) 
                                 VALUES 
                                 ('$assignment_uid', '$title', '$description', '$subject_id', '$teacher_id', '$deadline', NOW())");
  
  if($insert){
    $_SESSION['success'] = "Assignment created successfully!";
  } else {
    $_SESSION['error'] = "Failed to create assignment. Please try again.";
  }
  
  header("location:../assignments.php");
  exit();
}

// Update Assignment
if(isset($_POST['update_assignment'])){
  $assignment_uid = mysqli_real_escape_string($conn, $_POST['assignment_uid']);
  $title = mysqli_real_escape_string($conn, $_POST['title']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
  
  // Verify teacher owns this assignment
  $verify = mysqli_query($conn, "SELECT * FROM homework_assignments WHERE assignment_uid = '$assignment_uid' AND teacher_id = '$teacher_id'");
  
  if(mysqli_num_rows($verify) == 0){
    $_SESSION['error'] = "You don't have permission to update this assignment.";
    header("location:../assignments.php");
    exit();
  }
  
  // Update assignment
  $update = mysqli_query($conn, "UPDATE homework_assignments 
                                 SET assignment_title = '$title', 
                                     assignment_description = '$description', 
                                     deadline = '$deadline' 
                                 WHERE assignment_uid = '$assignment_uid'");
  
  if($update){
    $_SESSION['success'] = "Assignment updated successfully!";
  } else {
    $_SESSION['error'] = "Failed to update assignment.";
  }
  
  header("location:../assignments.php");
  exit();
}

// Delete Assignment
if(isset($_GET['delete_assignment'])){
  $assignment_uid = mysqli_real_escape_string($conn, $_GET['delete_assignment']);
  
  // Verify teacher owns this assignment
  $verify = mysqli_query($conn, "SELECT * FROM homework_assignments WHERE assignment_uid = '$assignment_uid' AND teacher_id = '$teacher_id'");
  
  if(mysqli_num_rows($verify) == 0){
    $_SESSION['error'] = "You don't have permission to delete this assignment.";
    header("location:../assignments.php");
    exit();
  }
  
  // Delete assignment and its submissions
  mysqli_query($conn, "DELETE FROM homework_submissions WHERE assignment_uid = '$assignment_uid'");
  $delete = mysqli_query($conn, "DELETE FROM homework_assignments WHERE assignment_uid = '$assignment_uid'");
  
  if($delete){
    $_SESSION['success'] = "Assignment deleted successfully!";
  } else {
    $_SESSION['error'] = "Failed to delete assignment.";
  }
  
  header("location:../assignments.php");
  exit();
}

// Default redirect
header("location:../assignments.php");
exit();
?>

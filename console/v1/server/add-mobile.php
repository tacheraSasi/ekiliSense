<?php 
session_start();
include_once "../../../config.php";

$school_unique_id = $_SESSION['School_uid'];
$phone = mysqli_real_escape_string($conn, $_POST['phone']);

$query = "UPDATE `schools` SET `school_phone` = '$phone' WHERE `schools`.`unique_id` = '$school_unique_id'";

$result = mysqli_query($conn, $query);

if ($result) {
echo"success";
}else{
    echo"Something went wrong. Try again.";
}
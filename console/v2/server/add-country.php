<?php 
session_start();
include_once "../../../config.php";


$school_unique_id = $_SESSION['School_uid'];
$country = mysqli_real_escape_string($conn, $_POST['country']);

$query = "UPDATE `schools` SET `country` = '$country' WHERE `schools`.`unique_id` = '$school_unique_id'";

$result = mysqli_query($conn, $query);

if ($result) {
    echo"success";
}else{
    echo"Something went wrong. Try again.";
}
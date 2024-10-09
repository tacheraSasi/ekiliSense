<?php
session_start();
include_once "../config.php";
if(!isset($_SESSION['School_uid'])){
  header("location:../auth");
}
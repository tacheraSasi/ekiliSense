<?php
  $hostname = "localhost";
  $username = "root";
  $password = "Tachy2004!";
  $dbname = "ekilie";

  $conn = mysqli_connect($hostname, $username, $password, $dbname);
  if(!$conn){
    echo "Database connection error".mysqli_connect_error();
  }
  // Setting the connection charset to handle emojis
  $conn->set_charset("utf8mb4");
?>

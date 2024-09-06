<?php
session_start();
if (isset($_SESSION['teacher_email'])) {
    include_once "../../../../config.php";
    $logout_id = $_GET['logout_id'];

    if (isset($logout_id)) {
        // Preparing the SQL statement
        $stmt = mysqli_prepare($conn, "SELECT * FROM teachers WHERE teacher_email = ?");
        mysqli_stmt_bind_param($stmt, "s", $logout_id);

        // Executing the statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            session_unset();
            session_destroy();
            // var_dump($_SESSION);
            header("Location: https://auth.ekilie.com/sense/teacher");
        } else {
            header("Location: ../../../404/");
        }

        // Closing the statement
        mysqli_stmt_close($stmt);
    } else {
        header("Location: https://auth.ekilie.com/sense/teacher");
    }
} else {
    header("Location: https://auth.ekilie.com/sense/teacher");
}

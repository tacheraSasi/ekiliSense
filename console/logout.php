<?php
session_start();
if (isset($_SESSION['School_uid'])) {
    include_once "../config.php";
    $ref = $_GET['ref'];

    if (isset($ref)) {
        // Preparing the SQL statement
        $stmt = mysqli_prepare($conn, "SELECT * FROM schools WHERE unique_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $ref);

        // Executing the statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            session_unset();
            session_destroy();
            // var_dump($_SESSION);
            header("Location: ../auth");
        } else {
            header("Location: ./404/");
        }

        // Closing the statement
        mysqli_stmt_close($stmt);
    } else {
        header("Location: ../auth");
    }
} else {
    header("Location: ../auth");
}

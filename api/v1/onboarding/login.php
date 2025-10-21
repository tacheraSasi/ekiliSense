<?php
session_start();
include_once "../../../config.php";

$data = json_decode(file_get_contents("php://input"));

$email = mysqli_real_escape_string($conn, $data->email);
$password = mysqli_real_escape_string($conn, $data->password);

if (!empty($email) && !empty($password)) {
    $sql = mysqli_query($conn, "SELECT * FROM schools WHERE school_email = '{$email}'");
    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
        if (password_verify($password, $row['auth'])) {
            $_SESSION['School_uid'] = $row['unique_id'];
            echo json_encode(["success" => "Login successful."]);
        } else {
            echo json_encode(["error" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["error" => "Email not found."]);
    }
} else {
    echo json_encode(["error" => "All input fields are required!"]);
}
?>
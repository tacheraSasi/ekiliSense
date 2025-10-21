<?php
session_start();
include_once "../../../config.php";

// Get the posted data.
$data = json_decode(file_get_contents("php://input"));

$instituteName = mysqli_real_escape_string($conn, $data->{'institute-name'});
$email = mysqli_real_escape_string($conn, $data->email);
$password = mysqli_real_escape_string($conn, $data->password);
$confirmPassword = mysqli_real_escape_string($conn, $data->{'confirm-password'});

if (!empty($instituteName) && !empty($email) && !empty($password)) {

    $sql = mysqli_query($conn, "SELECT * FROM schools WHERE school_email = '{$email}' OR school_name = '{$instituteName}'");
    if (mysqli_num_rows($sql) > 0) {
        echo json_encode(["error" => "Institute's name or email is unavailable or already taken."]);
    } else {
        if ($password == $confirmPassword) {
            $ran_id = rand(time(), 700000000);
            $status = "Active now";
            $encrypt_pass = password_hash($password, PASSWORD_BCRYPT);
            $insert_query = mysqli_query($conn, "INSERT INTO schools (unique_id, school_name, school_phone, school_email, auth, country)
            VALUES ({$ran_id}, '{$instituteName}', '0', '{$email}', '{$encrypt_pass}', NULL)");

            if ($insert_query) {
                $check = mysqli_query($conn, "SELECT * FROM schools WHERE school_email = '{$email}' AND school_name = '{$instituteName}'");
                if (mysqli_num_rows($check) > 0) {
                    $result = mysqli_fetch_assoc($check);
                    $_SESSION['School_uid'] = $result['unique_id'];
                    if (isset($_SESSION['School_uid'])) {
                        echo json_encode(["success" => "Account created successfully."]);
                    } else {
                        echo json_encode(["error" => "Something went wrong. Please try again later"]);
                    }
                } else {
                    echo json_encode(["error" => "Something went wrong. Please try again later."]);
                }
            } else {
                echo json_encode(["error" => "Something went wrong. Please try again later."]);
            }
        } else {
            echo json_encode(["error" => "Password does not match!"]);
        }
    }
} else {
    echo json_encode(["error" => "All input fields are required!"]);
}
?>
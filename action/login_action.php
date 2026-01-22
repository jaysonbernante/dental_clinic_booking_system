<?php
session_start();
include 'action/connection.php';  // Your DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // First, check users_managent table for admins/staff
    $stmt_mgmt = $connect->prepare("SELECT id, name, password, role FROM users_managent WHERE email = ? LIMIT 1");
    $stmt_mgmt->bind_param("s", $email);
    $stmt_mgmt->execute();
    $result_mgmt = $stmt_mgmt->get_result();

    if ($result_mgmt->num_rows > 0) {
        $user = $result_mgmt->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['role'];  // 'admin' or 'staff'
            header("Location: dentist/admin_dashboard.php");  // Fixed path
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        // Check dentists table for dentists
        $stmt_dentist = $connect->prepare("SELECT id, name, password FROM dentists WHERE email = ? LIMIT 1");
        $stmt_dentist->bind_param("s", $email);
        $stmt_dentist->execute();
        $result_dentist = $stmt_dentist->get_result();

        if ($result_dentist->num_rows > 0) {
            $dentist = $result_dentist->fetch_assoc();
            if (password_verify($password, $dentist['password'])) {
                $_SESSION['user_id'] = $dentist['id'];
                $_SESSION['user_name'] = $dentist['name'];
                $_SESSION['user_type'] = 'dentist';
                $_SESSION['dentist_id'] = $dentist['id'];  // Set for dentist pages
                header("Location: dentist/dentist_dashboard.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            // Check users table for patients
            $stmt = $connect->prepare("SELECT id, name, password FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_type'] = 'patient';
                    header("Location: patient/patient.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "Log in error: Your email and password do not match. Please try again.";
            }
        }
    }
}
?>
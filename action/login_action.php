<?php
// 1. Fix Session Notice: Only start if one isn't active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'action/connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $error = "";

    // STEP 1: Check users_managent table (Admin/Staff)
    $stmt_mgmt = $connect->prepare("SELECT id, name, password, role FROM users_managent WHERE email = ? LIMIT 1");
    $stmt_mgmt->bind_param("s", $email);
    $stmt_mgmt->execute();
    $result_mgmt = $stmt_mgmt->get_result();

    if ($result_mgmt->num_rows > 0) {
        $user = $result_mgmt->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['role']; 
            header("Location: dentist/dentist_dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        // STEP 2: Check for Patients first (to bypass the dentists table error)
        $stmt_pat = $connect->prepare("SELECT id, name, password, user_type FROM users WHERE email = ? LIMIT 1");
        $stmt_pat->bind_param("s", $email);
        $stmt_pat->execute();
        $result_pat = $stmt_pat->get_result();

        if ($result_pat->num_rows > 0) {
            $user = $result_pat->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type']; // Uses the column you just added!
                header("Location: patient/patient.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            // STEP 3: Check dentists table (Wrapped in a check to prevent crashing)
            // We check if the table exists before querying it
            $table_check = $connect->query("SHOW TABLES LIKE 'dentists'");
            if ($table_check->num_rows > 0) {
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
                        header("Location: dentist/dentist_dashboard.php");
                        exit();
                    } else {
                        $error = "Incorrect password.";
                    }
                } else {
                    $error = "User not found.";
                }
            } else {
                $error = "User not found (and Dentist system is offline).";
            }
        }
    }
}
?>
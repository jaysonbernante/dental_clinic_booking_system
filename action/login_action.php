<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'action/connection.php'; 

$error = "";
$lockout_seconds = 0; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $currentTime = date('Y-m-d H:i:s');

    // Helper to update attempts
    function handleFailedAttempt($connect, $table, $email, $current_attempts) {
        $new_attempts = $current_attempts + 1;
        $lockout = NULL;
        
        // 6 attempts = 5 min | 9 total attempts = 10 min
        if ($new_attempts >= 9) {
            $lockout = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        } elseif ($new_attempts >= 6) {
            $lockout = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        }

        $stmt = $connect->prepare("UPDATE $table SET login_attempts = ?, lockout_time = ? WHERE email = ?");
        $stmt->bind_param("iss", $new_attempts, $lockout, $email);
        $stmt->execute();
    }

    // Find user in both tables
    $user = null;
    $table = '';
    
    $stmt = $connect->prepare("SELECT id, name, password, role, login_attempts, lockout_time FROM users_management WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $table = 'users_management';
    } else {
        $stmt = $connect->prepare("SELECT id, name, password, user_type as role, login_attempts, lockout_time FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $table = 'users';
        }
    }

    if ($user) {
        // Auto-unlock if time passed
        if ($user['lockout_time'] && strtotime($currentTime) >= strtotime($user['lockout_time'])) {
            $connect->query("UPDATE $table SET lockout_time = NULL WHERE id = " . $user['id']);
            $user['lockout_time'] = NULL;
        }

        // Check if still locked
        if ($user['lockout_time'] && strtotime($user['lockout_time']) > strtotime($currentTime)) {
            $lockout_seconds = strtotime($user['lockout_time']) - strtotime($currentTime);
            $error = "locked"; 
        } 
        // ... sa loob ng iyong successful login block
if (password_verify($password, $user['password'])) {
    $connect->query("UPDATE $table SET login_attempts = 0, lockout_time = NULL WHERE id = " . $user['id']);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_type'] = $user['role']; 
    
    // MAGDAGDAG NG SUCCESS FLAG SA SESSION
    $_SESSION['login_success'] = true;

    $redirect = ($table == 'users') ? "patient/patient_medicalHistory.php" : "dentist/dentist_dashboard.php";
    header("Location: $redirect");
    exit();
}
        else {
            handleFailedAttempt($connect, $table, $email, $user['login_attempts']);
            $new_count = $user['login_attempts'] + 1;
            
            if ($new_count >= 6) {
                $error = "locked";
                // Recalculate seconds for immediate display after the failing click
                $lockout_seconds = ($new_count >= 9) ? 600 : 300;
            } else {
                $left = 6 - $new_count;
                $error = "Incorrect password. $left attempts remaining.";
            }
        }
    } else {
        $error = "User not found.";
    }
}
?>
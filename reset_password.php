<?php
session_start();
include 'action/connection.php';
include 'component/log_reg_top.php';

// Fix connection variable if needed
if (!isset($connect) && isset($conn)) { $connect = $conn; }

$error = '';
$success = '';

if(isset($_GET['token'])) {
    $token = $_GET['token'];
    $target_table = '';
    $user = null;

    // 1. First, check if the token belongs to a Patient (users table)
    $stmt = $connect->prepare("SELECT * FROM users WHERE reset_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        $target_table = 'users';
    } else {
        // 2. If not found, check if it belongs to an Admin/Staff (users_managent table)
        $stmt = $connect->prepare("SELECT * FROM users_managent WHERE reset_token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            $target_table = 'users_managent';
        }
    }

    // 3. Process the Password Update
    if($user) {
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if($password === $confirm) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update the correct table dynamically
                $stmt2 = $connect->prepare("UPDATE $target_table SET password = ?, reset_token = NULL WHERE id = ?");
                $stmt2->bind_param("si", $hashedPassword, $user['id']);
                $stmt2->execute();

                $success = "Password updated successfully for " . ($target_table == 'users' ? 'Patient' : 'Staff') . ".<br>
                <a href='login.php'>Login now</a>";
            } else {
                $error = "Passwords do not match.";
            }
        }
    } else {
        $error = "Invalid or expired token.";
    }
} else {
    $error = "No token provided.";
}
?>

<div class="auth-wrapper">
    <div class="fp-container">
        <h2>Reset Password</h2>
        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

        <?php if(!$success && !$error): ?>
        <form method="POST">
            <div class="mb-3">
                <label>New Password:</label>
                <input type="password" name="password" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'component/index_bottom.php'; ?>
<?php
session_start();
include 'action/connection.php';
include 'component/log_reg_top.php';
$error = '';
$success = '';

if(isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check token in database
    $stmt = $connect->prepare("SELECT * FROM users WHERE reset_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();

        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if($password === $confirm) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update password and remove token
                $stmt2 = $connect->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
                $stmt2->bind_param("si", $hashedPassword, $user['id']);
                $stmt2->execute();

                $success = "Password updated successfully.<br>
                <a href='login.php'>Login now</a>";
            } else {
                $error = "Passwords do not match.";
            }
        }

    } else {
        $error = "Invalid token.";
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

        <?php if(!$success && !$error) { ?>
        <form method="POST">
            <div class="mb-3">
                <label>New Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="mb-3">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <?php } ?>
    </div>
</div>
<?php include 'component/index_bottom.php'; ?>

<?php
session_start();
include 'action/connection.php';
include 'component/log_reg_top.php';

if (!isset($connect) && isset($conn)) { $connect = $conn; }

$error = '';
$success = '';
$currentTime = date('Y-m-d H:i:s');

if(isset($_GET['token'])) {
    $token = $_GET['token'];
    $target_table = '';
    $user = null;

    // 1. Search for token in both tables
    $tables = ['users', 'users_management'];
    foreach ($tables as $table) {
        $stmt = $connect->prepare("SELECT * FROM $table WHERE reset_token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            $target_table = $table;
            break;
        }
    }

    // 2. Check Expiration and Process Update
    if($user) {
        if (strtotime($currentTime) > strtotime($user['reset_token_expires'])) {
            $stmt_clear = $connect->prepare("UPDATE $target_table SET reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
            $stmt_clear->bind_param("i", $user['id']);
            $stmt_clear->execute();
            
            $error = "This reset link has expired (10-minute limit). Please request a new one.";
            $user = null; 
        } 
        elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if($password === $confirm) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $connect->prepare("UPDATE $target_table SET password = ?, reset_token = NULL, reset_token_expires = NULL, login_attempts = 0, lockout_time = NULL WHERE id = ?");
                $stmt2->bind_param("si", $hashedPassword, $user['id']);
                $stmt2->execute();

                $success = "Password updated successfully for " . ($target_table == 'users' ? 'Patient' : 'Staff') . ".";
            } else {
                $error = "Passwords do not match. Please try again.";
            }
        }
    } else {
        $error = "Invalid token or the link has already been used.";
    }
} else {
    $error = "No token provided.";
}
?>

<div class="auth-wrapper">
    <div class="fp-container" style="max-width: 450px; margin: 50px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h2 style="color: #ff6b9d; text-align: center; margin-bottom: 25px;">Reset Password</h2>
        
        <div id="auth-alert-container">
            <?php if ($error): ?>
                <div class="alert alert-danger" style="background: #fff5f5; color: #e53e3e; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #e53e3e;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
                <?php if(strpos($error, 'expired') !== false): ?>
                    <a href="forgot_password.php" class="btn-primary" style="display:block; text-align:center; text-decoration:none; background:#666; color:white; padding:10px; border-radius:8px; margin-bottom:20px;">Request New Link</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #f0fff4; color: #38a169; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #38a169;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    <a href="login.php" style="color: #ff6b9d; font-weight: 600; text-decoration: none;">Click here to Login</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if(!$success && $user): ?>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 8px;">New Password</label>
                <input type="password" name="password" required class="form-control" placeholder="Min 8 characters" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
            
            <div class="mb-4">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 8px;">Confirm Password</label>
                <input type="password" name="confirm_password" required class="form-control" placeholder="Repeat password"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; background: #ff6b9d; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s;">
                Update Password
            </button>
        </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="text-decoration: none; color: #666; font-size: 13px; font-weight: 500;">
                <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<?php include 'component/index_bottom.php'; ?>
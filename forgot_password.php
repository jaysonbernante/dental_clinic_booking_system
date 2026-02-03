<?php
session_start();
include 'action/connection.php'; // Ensure this uses your $connect variable
include 'component/log_reg_top.php';

// Load PHPMailer manually (no Composer)
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure $connect is defined
if (!isset($connect) && isset($conn)) { $connect = $conn; }

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $currentTime = date('Y-m-d H:i:s');
    $foundUser = null;
    $targetTable = '';

    // 1. Search for the email in both tables
    $tables = ['users', 'users_management'];
    
    foreach ($tables as $table) {
        $stmt = $connect->prepare("SELECT id, name, login_attempts, lockout_time FROM $table WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $foundUser = $result->fetch_assoc();
            $targetTable = $table;
            break; 
        }
    }

    if ($foundUser) {
        // 2. CHECK LOCKOUT STATUS
        if ($foundUser['lockout_time'] && strtotime($foundUser['lockout_time']) > strtotime($currentTime)) {
            $waitTime = ceil((strtotime($foundUser['lockout_time']) - strtotime($currentTime)) / 60);
            $error = "Too many attempts. Please wait $waitTime minutes before trying again.";
        } else {
            // 3. GENERATE TOKEN AND 10-MINUTE EXPIRATION
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime('+10 minutes'));

            // 4. PREPARE EMAIL
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'jaysonbernante@gmail.com'; 
                $mail->Password   = 'ouey daok qkgj zskx';      
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('jaysonbernante@gmail.com', 'Peter Dental Clinic');
                $mail->addAddress($email, $foundUser['name']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "
                    <h3>Password Reset Request</h3>
                    <p>Hi {$foundUser['name']},</p>
                    <p>You requested a password reset. Click the button below to set a new password. 
                    <strong>This link is only valid for 10 minutes.</strong></p>
                    <p><a href='http://localhost/dental_clinic_booking_system/reset_password.php?token={$token}' 
                    style='background:#0081C9; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
                    <p>If you did not request this, please ignore this email.</p>
                ";

                $mail->send();

                // 5. UPDATE DATABASE ON SUCCESS
                // Save token, expiry, and RESET attempts to 0
                $updateStmt = $connect->prepare("UPDATE $targetTable SET reset_token = ?, reset_token_expires = ?, login_attempts = 0, lockout_time = NULL WHERE email = ?");
                $updateStmt->bind_param("sss", $token, $expiry, $email);
                $updateStmt->execute();

                $success = "A reset link has been sent to your email. It expires in 10 minutes.";

            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    } else {
        // 6. EMAIL NOT FOUND: Track attempts for security
        // Even if email is wrong, we track based on the IP or the email entered to prevent spamming
        $error = "Email address not found in our records.";
        
        // Optional: Implement attempt tracking for non-existent emails if needed
    }

    // 7. HANDLE ATTEMPT INCREMENT ON LOCKOUT (If user exists but failed somehow)
    if ($foundUser && empty($success) && !isset($waitTime)) {
        $newAttempts = $foundUser['login_attempts'] + 1;
        $newLockout = NULL;

        if ($newAttempts >= 6) {
            $newLockout = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        } elseif ($newAttempts >= 3) {
            $newLockout = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        }

        $stmtAtt = $connect->prepare("UPDATE $targetTable SET login_attempts = ?, lockout_time = ? WHERE email = ?");
        $stmtAtt->bind_param("iss", $newAttempts, $newLockout, $email);
        $stmtAtt->execute();
    }
}
?>

<div class="auth-wrapper">
    <div class="fp-container" style="max-width: 450px; margin: 50px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h2 style="color: #ff6b9d; text-align: center; margin-bottom: 10px;">Forgot Password</h2>
        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 25px;">Enter your email and we'll send you a link to reset your password.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="background: #fff5f5; color: #e53e3e; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #e53e3e;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="background: #f0fff4; color: #38a169; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #38a169;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="email" style="display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 8px;">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="example@email.com" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s;"
                       onfocus="this.style.borderColor='#ff6b9d'; this.style.boxShadow='0 0 0 3px rgba(255,107,157,0.1)';"
                       onblur="this.style.borderColor='#ddd';">
            </div>
            
            <button type="submit" class="btn-primary" style="margin-top: 10px; width: 100%; background: #ff6b9d; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s;">
                Send Reset Link
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="text-decoration: none; color: #666; font-size: 13px; font-weight: 500;">
                <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<?php include 'component/index_bottom.php'; ?>
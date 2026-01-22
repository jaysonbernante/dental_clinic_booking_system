<?php

session_start();
include 'action/connection.php'; // Your DB connection
include 'component/log_reg_top.php';
// Load PHPMailer manually (no Composer)
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email']);

  // Check if the email exists in your users table
  $stmt = $connect->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Generate unique token
    $token = bin2hex(random_bytes(50));

    // Save token in database
    $stmt2 = $connect->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
    $stmt2->bind_param("ss", $token, $email);
    $stmt2->execute();

    // Prepare PHPMailer
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'jaysonbernante@gmail.com'; // your Gmail
      $mail->Password = 'ouey daok qkgj zskx';      // Gmail App Password
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      $mail->setFrom('jaysonbernante@gmail.com', 'Dental Clinic Booking');
      $mail->addAddress($email, $user['name']); // recipient

      $mail->isHTML(true);
      $mail->Subject = 'Password Reset Request';
      $mail->Body = "
                Hi {$user['name']},<br><br>
                You requested a password reset. Click the link below to reset your password:<br>
                <a href='http://localhost/dental_clinic_booking_system/reset_password.php?token={$token}'>Reset Password</a><br><br>
                If you did not request this, ignore this email.
            ";

      $mail->send();
      $success = "Reset link sent successfully! Please check your email.";
    } catch (Exception $e) {
      $error = "Mailer Error: " . $mail->ErrorInfo;
    }
  } else {
    $error = "Email not found.";
  }
}
?>

<div class="auth-wrapper">
  <div class="fp-container">
    <h2>Forgot Password</h2>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>


    <form method="POST" action="">
      <div class="mb-3">
        <label for="email">Enter your email:</label>
        <input type="email" name="email" id="email" required>
      </div>
      <div class="mb-2">
        <button type="submit" class="btn btn-primary">Send Reset Link</button>
      </div>
    </form>

    <div class="mb-2">
      <a href="login.php">Back to Login</a>
    </div>
  </div>
</div>

<?php include 'component/index_bottom.php'; ?>
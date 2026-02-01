<?php
session_start();
include 'connection.php';

// Load PHPMailer (Ensure the PHPMailer folder is in your root directory)
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Connection fix
if (!isset($connect)) {
    $connect = isset($conn) ? $conn : (isset($con) ? $con : null);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Check users_managent table
    $stmt = $connect->prepare("SELECT first_name, last_name FROM users_managent WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $full_name = $user['first_name'] . ' ' . $user['last_name'];
        $token = bin2hex(random_bytes(50));

        // Save token in database
        $stmt2 = $connect->prepare("UPDATE users_managent SET reset_token = ? WHERE email = ?");
        $stmt2->bind_param("ss", $token, $email);
        
        if ($stmt2->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'jaysonbernante@gmail.com'; 
                $mail->Password = 'ouey daok qkgj zskx';      
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('jaysonbernante@gmail.com', 'Peter Dental Clinic');
                $mail->addAddress($email, $full_name);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <div style='font-family: Arial; padding: 20px; border: 1px solid #eee;'>
                        <h3>Hello {$user['first_name']},</h3>
                        <p>We received a request to reset your password for your Dental Clinic account.</p>
                        <p>Click the link below to set a new password:</p>
                        <a href='http://localhost/dental_clinic_booking_system/reset_password.php?token={$token}' 
                           style='background: #0081C9; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                           Reset My Password
                        </a>
                        <p>If you did not request this, please ignore this email.</p>
                    </div>";

                $mail->send();
                echo "success";
            } catch (Exception $e) {
                echo "Mailer Error: " . $mail->ErrorInfo;
            }
        }
    } else {
        echo "Error: Email address not found in system.";
    }
}
?>
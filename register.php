<?php
session_start();
include 'component/log_reg_top.php';
include 'action/connection.php';
include 'action/register_action.php';
?>

<div class="auth-wrapper reveal active delay-200"> 
    <div class="register-container">
        <h2>Sign Up</h2>

        <div id="auth-alert-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" action="register.php" id="registerForm">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Juan Dela Cruz" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" id="registerBtn" class="btn-primary">Sign Up</button>
            
            <div style="text-align: center; margin-top: 20px;">
                <span style="font-size: 13px; color: #777;">Already have an account? </span>
                <a href="login.php" style="color: #ff6b9d; text-decoration: none; font-weight: 600;">Sign In</a>
            </div>
        </form>
    </div>
</div>

<?php include 'component/index_bottom.php'; ?>
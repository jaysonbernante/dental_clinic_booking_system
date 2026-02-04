<?php
session_start();
include 'component/log_reg_top.php';
include 'action/connection.php';
include 'action/login_action.php';
?>

<div class="auth-wrapper reveal active delay-200">
    <div class="login-container">
        <h2>Sign In</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div style="text-align: center; margin-bottom: 15px;">
                <a href="forgot_password.php" style=" font-size: 12px; color: #666; text-decoration: none;">
                    Forgot password? <span style="margin-left:5px;">&rarr;</span>
                </a>
            </div>

            <button type="submit" class="btn-submit">Login</button>
            
            <div style="text-align: center; margin-top: 20px;">
                <span style="font-size: 13px; color: #777;">Don't have an account? </span>
                <a href="register.php" style="color: #ff6b9d; text-decoration: none; font-weight: 600;">Sign Up</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: 'You can now sign in.',
            iconColor: '#ff6b9d',
        });
        // Linisin ang URL para hindi paulit-ulit ang toast
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    document.addEventListener('DOMContentLoaded', function() {
    // Kuhanin ang error mula sa PHP variable
    const loginError = "<?php echo $error; ?>";
    const isLocked = "<?php echo ($error === 'locked' || $lockout_seconds > 0) ? 'true' : 'false'; ?>";

    if (loginError && loginError !== "") {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Kung locked, pwedeng mas seryosong kulay (Warning)
        if (loginError.includes("locked") || isLocked === 'true') {
            Toast.fire({
                icon: 'warning',
                title: 'Account Locked',
                text: 'Please try again later.',
                iconColor: '#f39c12'
            });
        } else {
            // Normal login error (Incorrect password / User not found)
            Toast.fire({
                icon: 'error',
                title: 'Login Failed',
                text: loginError,
                iconColor: '#ff6b9d' // Pink theme
            });
        }
    }
});
</script>

<?php include 'component/index_bottom.php'; ?>
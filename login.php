<?php
session_start();
include 'component/log_reg_top.php';
include 'action/connection.php';
include 'action/login_action.php';
?>

<div class="auth-wrapper reveal delay-200">
  <div class="login-container">
    <h2 style="color: #ff6b9d; text-align: center; margin-bottom: 25px;">Sign In</h2>

    <div id="auth-alert-container">
        <?php if ($error === "locked"): ?>
            <div class="alert alert-danger" id="lockout-timer-msg" style="background: #fff5f5; color: #e53e3e; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #e53e3e;">
                <i class="fa-solid fa-clock-rotate-left"></i> Account locked. Please wait <span id="timer" style="font-weight: bold;"><?php echo $lockout_seconds; ?></span> seconds.
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger" style="background: #fff5f5; color: #e53e3e; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #e53e3e;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST" action="login.php" id="loginForm">
      <div class="mb-3">
        <label for="email" class="form-label" style="font-size: 13px; font-weight: 600; color: #444;">Email</label>
        <input type="email" name="email" class="form-control" id="email" required 
               style="border-radius: 8px; padding: 12px;"
               <?php echo ($error === "locked") ? 'disabled' : ''; ?>>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label" style="font-size: 13px; font-weight: 600; color: #444;">Password</label>
        <input type="password" name="password" class="form-control" id="password" required 
               style="border-radius: 8px; padding: 12px;"
               <?php echo ($error === "locked") ? 'disabled' : ''; ?>>
      </div>

      <div class="log-button">
        <a class="forgot" href="forgot_password.php" style="font-size: 12px; color: #666; text-decoration: none;">Forgot password?</a>
        
        <button type="submit" id="loginBtn" class="btn btn-primary w-100 mb-3" 
                style="background: #ff6b9d; border: none; padding: 12px; border-radius: 8px; font-weight: 600; margin-top: 15px;"
                <?php echo ($error === "locked") ? 'disabled' : ''; ?>>
            Login
        </button>
        
        <div style="text-align: center; margin-top: 10px;">
            <span style="font-size: 13px; color: #777;">Don't have an account? </span>
            <a href="register.php" style="color: #ff6b9d; font-size: 13px; font-weight: 600; text-decoration: none;">Sign Up</a>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
// Live Countdown Logic
const timerSpan = document.getElementById('timer');
const loginBtn = document.getElementById('loginBtn');
const inputs = document.querySelectorAll('#loginForm input');
const alertBox = document.getElementById('lockout-timer-msg');

if (timerSpan) {
    let seconds = parseInt(timerSpan.innerText);
    
    const interval = setInterval(() => {
        seconds--;
        
        if (seconds <= 0) {
            clearInterval(interval);
            // Switch to Success Styling
            alertBox.style.background = "#f0fff4";
            alertBox.style.color = "#38a169";
            alertBox.style.borderLeft = "4px solid #38a169";
            alertBox.innerHTML = "<i class='fa-solid fa-circle-check'></i> Security lockout lifted. You can login again.";
            
            // Re-enable Form
            loginBtn.disabled = false;
            inputs.forEach(input => input.disabled = false);
        } else {
            timerSpan.innerText = seconds;
        }
    }, 1000);
}
</script>

<?php
include 'component/index_bottom.php';
?>
<?php
session_start();
include 'component/log_reg_top.php'; 
include 'action/connection.php'; 
include 'action/login_action.php'; 

?>
<div class=" auth-wrapper reveal delay-200">
<div class="login-container ">
  <h2>Sign In</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="email" class="form-control" id="email" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" class="form-control" id="password" required>
    </div>
      <div class="log-button">
      <a class="forgot" href="forgot_password.php">Forgot password?</a>
      <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
      <a href="register.php" class="btn">Sign Up </a>
    </div>
  </form>
  
</div>
</div>

<?php 
include 'component/index_bottom.php'; 
?>

<?php
session_start();
include 'component/log_reg_top.php';
include 'action/connection.php';
include 'action/register_action.php';

?>
<div class=" auth-wrapper reveal delay-200">
  <div class="register-container ">
    <h2>Sign Up</h2>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="mb-3">
      <label for="name" class="form-label">Full Name</label>
      <input type="text" name="name" class="form-control" id="name" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="email" class="form-control" id="email" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" class="form-control" id="password" required>
    </div>

    <div class="mb-3">
      <label for="confirm_password" class="form-label">Confirm Password</label>
      <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
    </div>

      <div class="reg-button">
        <a class="forgot" href="forgot_password.php">Forgot password?</a>
        <button type="submit" class="btn btn-primary w-100 mb-3">Sign Up</button>
        <a href="login.php" class="btn">Sign in</a>
      </div>
    </form>

  </div>
</div>

<?php
include 'component/index_bottom.php';
?>
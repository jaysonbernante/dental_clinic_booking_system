<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';


$user_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $dental_history = trim($_POST['dental_history']);
    $last_dental_visit = $_POST['last_dental_visit'];

    // Optional: Validate email is unique
    $stmt_check = $connect->prepare("SELECT id FROM users WHERE email=? AND id<>?");
    $stmt_check->bind_param("si", $email, $user_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        $error = "Email is already taken.";
    } else {
        $stmt = $connect->prepare("UPDATE users SET name=?, email=?, contact=?, dental_history=?, last_dental_visit=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $contact, $dental_history, $last_dental_visit, $user_id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile. Try again.";
        }
    }
}

// Fetch user data
$stmt = $connect->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<?php
session_start();
if (isset($_SESSION['profile_required'])) {
    $error = $_SESSION['profile_required'];
    unset($_SESSION['profile_required']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile | Peter Dental</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Your CSS -->
    <link rel="stylesheet" href="../assets/css/patient.css">
    <style>
        /* Toast Notification */
        .toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 9999;
            left: 50%;
            bottom: 30px;
            font-size: 16px;
            opacity: 0;
            transition: opacity 0.5s, bottom 0.5s;
        }
        .toast.success { background-color: #4BB543; }
        .toast.error { background-color: #FF3333; }

        .form-card { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background: #fff; }
        .form-card label { display: block; margin: 10px 0 5px; }
        .form-card input, .form-card textarea { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
        .form-card button { margin-top: 15px; padding: 10px 15px; border: none; border-radius: 5px; background-color: #4BB543; color: #fff; cursor: pointer; }
    </style>
</head>
<body>

<?php include '../component/patient_navbar.php'; ?>

<!-- Toast Message -->
<div id="toast" class="toast <?php echo isset($success) ? 'success' : (isset($error) ? 'error' : ''); ?>">
    <?php
        if (isset($success)) echo $success;
        if (isset($error)) echo $error;
    ?>
</div>

<main class="container" style="padding-top: 140px;">
    <h2>My Profile</h2>

    <form action="profile.php" method="POST" class="form-card">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Contact</label>
        <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>">

        <label>Dental History</label>
        <textarea name="dental_history"><?php echo htmlspecialchars($user['dental_history']); ?></textarea>

        <label>Last Dental Visit</label>
        <input type="date" name="last_dental_visit" value="<?php echo $user['last_dental_visit']; ?>">

        <button type="submit" name="update_profile">Save Changes</button>
    </form>
</main>

<!-- Toast JS -->
<script>
window.onload = function() {
    const toast = document.getElementById('toast');
    if (toast && toast.textContent.trim() !== '') {
        toast.style.visibility = 'visible';
        toast.style.opacity = '1';
        toast.style.bottom = '50px';

        // Hide after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.bottom = '30px';
            setTimeout(() => { toast.style.visibility = 'hidden'; }, 500);
        }, 3000);
    }
}
</script>

</body>
</html>

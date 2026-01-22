<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

// Handle add staff
if (isset($_POST['add_staff'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $connect->prepare("INSERT INTO users_managent (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    if ($stmt->execute()) {
        $success = "Staff account created successfully!";
    } else {
        $error = "Failed to create account. Email may already exist.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $connect->query("DELETE FROM users_managent WHERE id=$id AND role='staff'");  // Only allow deleting staff, not admins
    $success = "Staff deleted!";
}

// Fetch staff (exclude current admin for safety)
$query = "SELECT id, name, email, role FROM users_managent WHERE id != ? ORDER BY created_at DESC";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$staff = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff | Peter Dental</title>
    <!-- Same head as your admin pages -->
    <style>
        .form-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .form-card label { display: block; margin: 10px 0 5px; }
        .form-card input, .form-card select { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
        .form-card button { margin-top: 15px; padding: 10px 15px; border: none; border-radius: 5px; background-color: #4BB543; color: #fff; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; }
        .btn.delete { background-color: #FF3333; color: white; }
        
    </style>
</head>
<body>
    <?php include '../component/dentist_sidebar.php'; ?> 

    <div id="toast" class="toast <?php echo isset($success) ? 'success' : (isset($error) ? 'error' : ''); ?>">
        <?php if (isset($success)) echo $success; if (isset($error)) echo $error; ?>
    </div>

    <main class="container" style="padding-top: 140px;">
        <h2>Manage Staff Accounts</h2>

        <div class="form-card">
            <h4>Create New Staff Account</h4>
            <form method="POST">
                <label>Name</label>
                <input type="text" name="name" required>
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <label>Role</label>
                <select name="role">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>  <!-- Allow creating other admins if needed -->
                </select>
                <button type="submit" name="add_staff">Create Account</button>
            </form>
        </div>

        <h4>Existing Staff</h4>
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($row = $staff->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo ucfirst($row['role']); ?></td>
                        <td>
                            <button class="btn" onclick="editStaff(<?php echo $row['id']; ?>)">Edit</button>
                            <?php if ($row['role'] !== 'admin'): ?>  <!-- Prevent deleting admins -->
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn delete" onclick="return confirm('Delete this staff member?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <script>
        // Toast JS (same as your code)
        window.onload = function() {
            const toast = document.getElementById('toast');
            if (toast && toast.textContent.trim() !== '') {
                toast.style.visibility = 'visible';
                toast.style.opacity = '1';
                toast.style.bottom = '50px';
                setTimeout(() => { toast.style.opacity = '0'; toast.style.bottom = '30px'; setTimeout(() => { toast.style.visibility = 'hidden'; }, 500); }, 3000);
            }
        };

        function editStaff(id) {
            // Implement edit: Redirect to edit form or use modal to load/update data
            alert('Edit functionality: Redirect to edit page for ID ' + id);
            // Example: window.location.href = 'edit_staff.php?id=' + id;
        }
    </script>
</body>
</html>
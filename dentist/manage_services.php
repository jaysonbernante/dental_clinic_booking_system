<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

// Handle add service
if (isset($_POST['add_service'])) {
    $name = trim($_POST['service_name']);
    $description = trim($_POST['description']); // Note: Your schema doesn't have description; add if needed: ALTER TABLE services ADD COLUMN description TEXT;
    $duration = $_POST['duration'];
    $price = $_POST['price'];

    $stmt = $connect->prepare("INSERT INTO services (service_name, duration, price) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $name, $duration, $price);
    if ($stmt->execute()) {
        $success = "Service added successfully!";
    } else {
        $error = "Failed to add service.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $connect->query("DELETE FROM services WHERE id=$id");
    $success = "Service deleted!";
}

// Fetch services
$services = $connect->query("SELECT * FROM services");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services | Peter Dental</title>
    <!-- Same head as dentist_dashboard.php -->
    <style>
        .form-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .form-card label { display: block; margin: 10px 0 5px; }
        .form-card input { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
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
        <h2>Manage Services</h2>

        <div class="form-card">
            <h4>Add New Service</h4>
            <form method="POST">
                <label>Service Name</label>
                <input type="text" name="service_name" required>
                <label>Description</label>
                <textarea name="description"></textarea> <!-- Add to schema if needed -->
                <label>Estimated Duration (minutes)</label>
                <input type="number" name="duration" required>
                <label>Price</label>
                <input type="number" step="0.01" name="price" required>
                <button type="submit" name="add_service">Add Service</button>
            </form>
        </div>

        <h4>Services List</h4>
        <table>
            <thead>
                <tr><th>Name</th><th>Duration</th><th>Price</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($row = $services->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                        <td><?php echo $row['duration']; ?> min</td>
                        <td>$<?php echo $row['price']; ?></td>
                        <td>
                            <button class="btn" onclick="editService(<?php echo $row['id']; ?>)">Edit</button>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn delete" onclick="return confirm('Delete this service?')">Delete</a>
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

        function editService(id) {
            // Implement edit modal or redirect to edit form (e.g., load data into form)
            alert('Edit functionality: Load service data for ID ' + id);
        }
    </script>
</body>
</html>
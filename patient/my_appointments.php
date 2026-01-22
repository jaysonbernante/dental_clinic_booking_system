<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

// Handle cancel appointment
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the user can only cancel their own appointment
    $stmt = $connect->prepare("UPDATE appointments SET status='Canceled' WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $cancel_id, $user_id);
    if ($stmt->execute()) {
        $success = "Appointment canceled successfully!";
    } else {
        $error = "Failed to cancel appointment. Try again.";
    }
}

// Fetch appointments for logged-in user (updated to show all services)
$user_id = $_SESSION['user_id'];
$query = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, 
                 d.name AS dentist_name, GROUP_CONCAT(s.service_name SEPARATOR ', ') AS service_names
          FROM appointments a
          JOIN dentists d ON a.dentist_id = d.id
          JOIN appointment_services asv ON a.id = asv.appointment_id
          JOIN services s ON asv.service_id = s.id
          WHERE a.user_id = ?
          GROUP BY a.id
          ORDER BY a.appointment_date, a.appointment_time";

$stmt = $connect->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appointments | Peter Dental</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; }
        .btn.cancel { background-color: #FF3333; color: white; }
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
    <h2>My Appointments</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Dentist</th>
                    <th>Services</th>  <!-- Changed to Services (plural) -->
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date("M d, Y", strtotime($row['appointment_date'])); ?></td>
                        <td><?php echo date("h:i A", strtotime($row['appointment_time'])); ?></td>
                        <td><?php echo htmlspecialchars($row['dentist_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['service_names']); ?></td>  <!-- Now shows all services -->
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <?php if($row['status'] == 'Pending' || $row['status'] == 'Confirmed'): ?>
                                <a href="my_appointments.php?cancel_id=<?php echo $row['id']; ?>" class="btn cancel" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No appointments found.</p>
    <?php endif; ?>

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
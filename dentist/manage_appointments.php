<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    if ($action == 'approve') $status = 'Confirmed';
    elseif ($action == 'reject') $status = 'Canceled';
    $stmt = $connect->prepare("UPDATE appointments SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

// Fetch all appointments (updated to show all services)
$query = "SELECT a.id, u.name AS patient, d.name AS dentist, GROUP_CONCAT(s.service_name SEPARATOR ', ') AS service_names, a.appointment_date, a.appointment_time, a.status
          FROM appointments a
          JOIN users u ON a.user_id = u.id
          JOIN dentists d ON a.dentist_id = d.id
          JOIN appointment_services asv ON a.id = asv.appointment_id
          JOIN services s ON asv.service_id = s.id
          GROUP BY a.id
          ORDER BY a.appointment_date DESC";
$result = $connect->query($query);
?>

<?php include '../component/dentist_sidebar.php'; ?>  <!-- Use admin_navbar.php for admin pages -->

<main class="container" style="padding-top: 140px;">
    <h2>Manage Appointments</h2>
    <table>
        <thead><tr><th>Patient</th><th>Dentist</th><th>Services</th><th>Date & Time</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['patient']); ?></td>
                    <td><?php echo htmlspecialchars($row['dentist']); ?></td>
                    <td><?php echo htmlspecialchars($row['service_names']); ?></td>  <!-- Now shows all services -->
                    <td><?php echo $row['appointment_date'] . ' ' . $row['appointment_time']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <?php if ($row['status'] == 'Pending'): ?>
                            <a href="?action=approve&id=<?php echo $row['id']; ?>">Approve</a> |
                            <a href="?action=reject&id=<?php echo $row['id']; ?>">Reject</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
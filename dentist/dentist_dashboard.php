<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'dentist') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

$dentist_id = $_SESSION['dentist_id'] ?? null;
if (!$dentist_id) {
    echo "Dentist ID not found. Please contact admin.";
    exit();
}

// Fetch all appointments for this dentist (updated to show all services)
$query = "SELECT a.id, a.appointment_date, a.appointment_time, u.name AS patient_name, GROUP_CONCAT(s.service_name SEPARATOR ', ') AS service_names, a.status
          FROM appointments a
          JOIN users u ON a.user_id = u.id
          JOIN appointment_services asv ON a.id = asv.appointment_id
          JOIN services s ON asv.service_id = s.id
          WHERE a.dentist_id = ?
          GROUP BY a.id
          ORDER BY a.appointment_date, a.appointment_time";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $dentist_id);
$stmt->execute();
$appointments = $stmt->get_result();

// Status overview
$status_counts = [];
$query_status = "SELECT status, COUNT(*) as count FROM appointments WHERE dentist_id = ? GROUP BY status";
$stmt_status = $connect->prepare($query_status);
$stmt_status->bind_param("i", $dentist_id);
$stmt_status->execute();
$result_status = $stmt_status->get_result();
while ($row = $result_status->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dentist Dashboard | Peter Dental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/patient.css">
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f8f9fa; }
        main.container { max-width: 900px; margin: auto; padding-top: 140px; }
        h2 { font-family: 'Poppins', sans-serif; font-weight: 600; margin-bottom: 20px; color: #333; }
        .stats { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
        .card { background: white; flex: 1 1 200px; padding: 25px 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .card h4 { font-size: 18px; font-weight: 600; color: #555; margin-bottom: 10px; }
        .card p { font-size: 16px; color: #777; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <?php include '../component/dentist_sidebar.php'; ?>

    <main class="container">
        <h2>Welcome, Dr. <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h2>

        <div class="stats">
            <div class="card">
                <h4><i class="fa-solid fa-calendar-day"></i> Total Appointments</h4>
                <p><?php echo $appointments->num_rows; ?> scheduled</p>
            </div>
            <div class="card">
                <h4><i class="fa-solid fa-chart-bar"></i> Status Overview</h4>
                <p>Pending: <?php echo $status_counts['Pending'] ?? 0; ?><br>Confirmed: <?php echo $status_counts['Confirmed'] ?? 0; ?><br>Completed: <?php echo $status_counts['Completed'] ?? 0; ?></p>
            </div>
        </div>

        <h3>All Appointments</h3>
        <table>
            <thead>
                <tr><th>Date</th><th>Time</th><th>Patient</th><th>Services</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date("M d, Y", strtotime($row['appointment_date'])); ?></td>
                        <td><?php echo date("h:i A", strtotime($row['appointment_time'])); ?></td>
                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['service_names']); ?></td>  <!-- Now shows all services -->
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
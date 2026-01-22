<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

// Handle filters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$dentist_id = $_GET['dentist_id'] ?? null;

// Appointment Reports
$query_appts = "SELECT COUNT(*) as total, status FROM appointments WHERE appointment_date BETWEEN ? AND ?";
$params = [$date_from, $date_to];
$types = "ss";
if ($dentist_id) {
    $query_appts .= " AND dentist_id = ?";
    $params[] = $dentist_id;
    $types .= "i";
}
$stmt = $connect->prepare($query_appts);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$appt_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Patient Activity Logs (e.g., recent logins or visits - assume a logs table or use appointments)
$query_logs = "SELECT u.name, a.appointment_date FROM appointments a JOIN users u ON a.user_id = u.id WHERE a.appointment_date BETWEEN ? AND ? ORDER BY a.appointment_date DESC LIMIT 10";
$stmt_logs = $connect->prepare($query_logs);
$stmt_logs->bind_param("ss", $date_from, $date_to);
$stmt_logs->execute();
$logs = $stmt_logs->get_result();

// Clinic Performance Summary (e.g., total revenue, avg duration)
$query_perf = "SELECT SUM(s.price) as revenue, AVG(s.duration) as avg_duration FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.appointment_date BETWEEN ? AND ?";
$stmt_perf = $connect->prepare($query_perf);
$stmt_perf->bind_param("ss", $date_from, $date_to);
$stmt_perf->execute();
$perf = $stmt_perf->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports | Peter Dental</title>
    <!-- Same head -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .filters { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .report-section { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; background-color: #4BB543; color: white; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <?php include '../component/dentist_sidebar.php'; ?> 

    <main class="container" style="padding-top: 140px;">
        <h2>Reports</h2>

        <div class="filters">
            <form method="GET">
                <label>Date From</label><input type="date" name="date_from" value="<?php echo $date_from; ?>">
                <label>Date To</label><input type="date" name="date_to" value="<?php echo $date_to; ?>">
                <label>Dentist</label>
                <select name="dentist_id">
                    <option value="">All</option>
                    <?php $dentists = $connect->query("SELECT id, name FROM dentists"); while ($d = $dentists->fetch_assoc()) echo "<option value='{$d['id']}'>{$d['name']}</option>"; ?>
                </select>
                <button type="submit" class="btn">Generate</button>
            </form>
        </div>

        <div class="report-section">
            <h4>Appointment Reports</h4>
            <canvas id="apptChart"></canvas>
        </div>

        <div class="report-section">
            <h4>Patient Activity Logs</h4>
            <table>
                <thead><tr><th>Patient</th><th>Date</th></tr></thead>
                <tbody><?php while ($log = $logs->fetch_assoc()) echo "<tr><td>{$log['name']}</td><td>{$log['appointment_date']}</td></tr>"; ?></tbody>
            </table>
        </div>

        <div class="report-section">
            <h4>Clinic Performance Summary</h4>
            <p>Total Revenue: $<?php echo $perf['revenue'] ?? 0; ?></p>
            <p>Avg Service Duration: <?php echo round($perf['avg_duration'] ?? 0, 2); ?> min</p>
        </div>

        <button class="btn" onclick="exportPDF()">Export to PDF</button>
        <button class="btn" onclick="window.print()">Print</button>
    </main>

    <script>
        // Chart for appointments
        const ctx = document.getElementById('apptChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Confirmed', 'Completed', 'Canceled'],
                datasets: [{
                    label: 'Appointments',
                    data: [
                        <?php echo array_sum(array_column(array_filter($appt_data, fn($a) => $a['status'] == 'Pending'), 'total')); ?>,
                        <?php echo array_sum(array_column(array_filter($appt_data, fn($a) => $a['status'] == 'Confirmed'), 'total')); ?>,
                        <?php echo array_sum(array_column(array_filter($appt_data, fn($a) => $a['status'] == 'Completed'), 'total')); ?>,
                        <?php echo array_sum(array_column(array_filter($appt_data, fn($a) => $a['status'] == 'Canceled'), 'total')); ?>
                    ]
                }]
            }
        });

        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text('Clinic Reports', 10, 10);
            // Add more content from DOM
            doc.save('reports.pdf');
        }
    </script>
</body>
</html>
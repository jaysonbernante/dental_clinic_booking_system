<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

// Stats
$total_appointments = $connect->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$pending_requests = $connect->query("SELECT COUNT(*) as count FROM appointments WHERE status='Pending'")->fetch_assoc()['count'];
$daily_patients = $connect->query("SELECT COUNT(DISTINCT user_id) as count FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['count'];
?>


    <?php include '../component/dentist_sidebar.php'; ?> 

    <main class="container">
        <h2>Admin Dashboard</h2>
        <div class="stats">
            <div class="card"><h4>Total Appointments</h4><p><?php echo $total_appointments; ?></p></div>
            <div class="card"><h4>Pending Requests</h4><p><?php echo $pending_requests; ?></p></div>
            <div class="card"><h4>Daily Patients</h4><p><?php echo $daily_patients; ?></p></div>
        </div>
    </main>
</body>
</html>
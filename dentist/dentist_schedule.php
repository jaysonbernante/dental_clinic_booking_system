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

// Handle save schedule
if (isset($_POST['save_schedule'])) {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $is_off = isset($_POST['is_off']) ? 1 : 0;

    $stmt = $connect->prepare("INSERT INTO dentist_schedules (dentist_id, schedule_date, start_time, end_time, is_off) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE start_time=VALUES(start_time), end_time=VALUES(end_time), is_off=VALUES(is_off)");
    $stmt->bind_param("isssi", $dentist_id, $date, $start_time, $end_time, $is_off);
    if ($stmt->execute()) {
        $success = "Schedule updated!";
    } else {
        $error = "Failed to update schedule.";
    }
}

// Fetch existing schedules for calendar
$schedules = [];
$query = "SELECT schedule_date, start_time, end_time, is_off FROM dentist_schedules WHERE dentist_id = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $dentist_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedule | Peter Dental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/patient.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f8f9fa; }
        main.container { max-width: 900px; margin: auto; padding-top: 140px; }
        h2 { font-family: 'Poppins', sans-serif; font-weight: 600; margin-bottom: 20px; color: #333; }
        #calendar { max-width: 900px; margin: 0 auto; }
        .form-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-top: 20px; }
        .toast { visibility: hidden; min-width: 250px; margin-left: -125px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 16px; position: fixed; z-index: 9999; left: 50%; bottom: 30px; font-size: 16px; opacity: 0; transition: opacity 0.5s, bottom 0.5s; }
        .toast.success { background-color: #4BB543; }
        .toast.error { background-color: #FF3333; }
    </style>
</head>
<body>
    <?php include '../component/dentist_sidebar.php'; ?>

    <div id="toast" class="toast <?php echo isset($success) ? 'success' : (isset($error) ? 'error' : ''); ?>"><?php if (isset($success)) echo $success; if (isset($error)) echo $error; ?></div>

    <main class="container">
        <h2>Manage My Schedule</h2>
        <div id='calendar'></div>

        <div class="form-card">
            <h4>Set Hours for Selected Date</h4>
            <form method="POST">
                <input type="date" name="date" id="selected-date" required>
                <input type="time" name="start_time" placeholder="Start Time">
                <input type="time" name="end_time" placeholder="End Time">
                <label><input type="checkbox" name="is_off"> Day Off</label>
                <button type="submit" name="save_schedule">Save</button>
            </form>
        </div>
    </main>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                dateClick: function(info) {
                    document.getElementById('selected-date').value = info.dateStr;
                },
                events: <?php echo json_encode(array_map(function($s) {
                    return [
                        'title' => $s['is_off'] ? 'Off' : $s['start_time'] . '-' . $s['end_time'],
                        'start' => $s['schedule_date'],
                        'color' => $s['is_off'] ? 'red' : 'green'
                    ];
                }, $schedules)); ?>
            });
            calendar.render();
        });

        // Toast JS
        window.onload = function() {
            const toast = document.getElementById('toast');
            if (toast && toast.textContent.trim() !== '') {
                toast.style.visibility = 'visible';
                toast.style.opacity = '1';
                toast.style.bottom = '50px';
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.bottom = '30px';
                    setTimeout(() => { toast.style.visibility = 'hidden'; }, 500);
                }, 3000);
            }
        };
    </script>
</body>
</html>
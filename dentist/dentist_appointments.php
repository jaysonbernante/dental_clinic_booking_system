<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';
if (!isset($conn)) { $conn = $connect ?? $con ?? die("Database connection failed."); }

$user_id = $_SESSION['user_id'];

// Logic for tabs
$tab = $_GET['tab'] ?? 'requests';
$subtab = $_GET['sub'] ?? 'pending';
$date_filter = $_GET['date'] ?? null; // Filter para sa piniling date mula sa calendar

// --- DATABASE LOGIC ---
$status_map = [
    'pending'   => 'Pending',
    'deferred'  => 'Deferred',
    'confirmed' => 'Confirmed',
    'completed' => 'Completed'
];

$status_filter = $status_map[$subtab] ?? 'Pending';

// --- AUTO-UPDATE NOTIF STATUS ---
if (isset($_GET['sub']) && ($subtab == 'pending' || $subtab == 'deferred')) {
    $update_query = "UPDATE appointments SET is_read = 1 WHERE status = '$status_filter' AND is_read = 0";
    mysqli_query($conn, $update_query);
}

// --- QUERY FOR APPOINTMENTS LIST ---
$where_clause = "WHERE a.status = '$status_filter'";
if ($date_filter) {
    $where_clause .= " AND a.appointment_date = '" . mysqli_real_escape_string($conn, $date_filter) . "'";
}

$query = "SELECT a.*, u.name as patient_name 
          FROM appointments a 
          INNER JOIN users u ON a.user_id = u.id 
          $where_clause 
          ORDER BY a.appointment_date ASC, a.appointment_time ASC";
$appointments_result = mysqli_query($conn, $query);

// --- CALENDAR LOGIC ---
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day_of_month);
$first_day_weekday = date('w', $first_day_of_month);

// Kunin ang mga dates na may Confirmed appointments para sa dots
$booked_dates = [];
$booked_query = "SELECT DISTINCT appointment_date FROM appointments WHERE status = 'Confirmed'";
$booked_res = mysqli_query($conn, $booked_query);
while($row_booked = mysqli_fetch_assoc($booked_res)) {
    $booked_dates[] = $row_booked['appointment_date'];
}

// Kunin ang mga Disabled Dates (Holidays/Day-offs)
$disabled_dates = [];
$res_dis = mysqli_query($conn, "SELECT disabled_date FROM disabled_dates");
while($r = mysqli_fetch_assoc($res_dis)) { 
    $disabled_dates[] = $r['disabled_date']; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Peter Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --peter-pink: #ff6b9d; --peter-blue: #0081C9; --danger: #ff4d4d; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: #f8f9fa; display: flex; }
        .main-container { margin-left: 260px; flex: 1; padding: 20px; width: calc(100% - 260px); }
        
        .tabs-nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 8px 20px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; background: white; color: var(--peter-pink); border: 1px solid #eee; transition: 0.3s; }
        .tab-btn.active { background: var(--peter-pink); color: white; border-color: var(--peter-pink); }
        
        .sub-tabs { display: flex; gap: 20px; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        .sub-btn { padding: 10px 5px; text-decoration: none; font-size: 14px; color: #777; border-bottom: 2px solid transparent; }
        .sub-btn.active { color: var(--peter-pink); border-bottom: 2px solid var(--peter-pink); font-weight: 700; }
        
        .content-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); min-height: 400px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; color: #aaa; text-transform: uppercase; padding: 12px; border-bottom: 1px solid #eee; }
        td { padding: 15px 12px; font-size: 14px; border-bottom: 1px solid #fafafa; }
        
        .btn-action { padding: 5px 10px; border-radius: 5px; border: none; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-approve { background: #e8f5e9; color: #2e7d32; }
        .btn-defer { background: #fff3e0; color: #ef6c00; }

        /* Calendar Styles */
        .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); border-top: 1px solid #eee; border-left: 1px solid #eee; border-radius: 10px; overflow: hidden; }
        .cal-day-head { background: #f8f9fa; padding: 15px; text-align: center; font-weight: 600; font-size: 12px; color: #aaa; border-right: 1px solid #eee; border-bottom: 1px solid #eee; }
        .cal-day { min-height: 110px; padding: 10px; background: white; border-right: 1px solid #eee; border-bottom: 1px solid #eee; cursor: pointer; transition: 0.2s; position: relative; }
        .cal-day:hover { background: #fff5f8; }
        .cal-day.today { background: #fff0f5; }
        .cal-day.disabled-day { background: #ffebee; }
        .cal-day.disabled-day .day-num { color: var(--danger); }
        
        .day-num { font-weight: 600; color: #555; font-size: 14px; }
        .status-icons { display: flex; flex-direction: column; gap: 4px; margin-top: 8px; align-items: center; }
        .apt-dot { width: 8px; height: 8px; background: var(--peter-pink); border-radius: 50%; box-shadow: 0 0 5px rgba(255,107,157,0.5); }
        .off-label { font-size: 9px; background: var(--danger); color: white; padding: 2px 5px; border-radius: 4px; font-weight: 700; }
        
        .cal-nav-btn { background: white; border: 1px solid #eee; padding: 8px 15px; border-radius: 8px; color: var(--peter-pink); text-decoration: none; transition: 0.3s; }
        .filter-info { background: #fff3e0; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #ff9800; }
        .legend { display: flex; gap: 15px; font-size: 11px; margin-bottom: 15px; color: #888; }
    </style>
</head>
<body>

    <?php include '../component/sideBar_dentist.php'; ?>

    <div class="main-container">
        <?php $pageTitle = "Appointments"; include '../component/headerTop_dentist.php'; ?>

        <div class="tabs-nav">
            <a href="?tab=requests" class="tab-btn <?php echo $tab == 'requests' ? 'active' : ''; ?>">Requests</a>
            <a href="?tab=calendar" class="tab-btn <?php echo $tab == 'calendar' ? 'active' : ''; ?>">Calendar</a>
            <a href="?tab=search" class="tab-btn <?php echo $tab == 'search' ? 'active' : ''; ?>">Search</a>
        </div>

        <div class="content-card">
            
            <?php if ($tab == 'requests'): ?>
                <div class="sub-tabs">
                    <a href="?tab=requests&sub=pending" class="sub-btn <?php echo $subtab == 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?tab=requests&sub=confirmed" class="sub-btn <?php echo $subtab == 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                    <a href="?tab=requests&sub=deferred" class="sub-btn <?php echo $subtab == 'deferred' ? 'active' : ''; ?>">Deferred</a>
                    <a href="?tab=requests&sub=completed" class="sub-btn <?php echo $subtab == 'completed' ? 'active' : ''; ?>">Completed</a>
                </div>

                <?php if ($date_filter): ?>
                    <div class="filter-info">
                        <span><i class="fa-solid fa-calendar-day"></i> Showing Confirmed for: <b><?php echo date('M d, Y', strtotime($date_filter)); ?></b></span>
                        <a href="?tab=requests&sub=confirmed" style="color: #ef6c00; font-weight: 700; font-size: 12px; text-decoration:none;">CLEAR FILTER [X]</a>
                    </div>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>Scheduled Date</th>
                            <th>Patient</th>
                            <th>Reason for Visit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($appointments_result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($appointments_result)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></strong><br>
                                        <small style="color: #888;"><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td>
                                        <?php if($subtab == 'pending'): ?>
                                            <button class="btn-action btn-approve" onclick="updateStatus(<?php echo $row['id']; ?>, 'Confirmed')">Confirm</button>
                                            <button class="btn-action btn-defer" onclick="updateStatus(<?php echo $row['id']; ?>, 'Deferred')">Defer</button>
                                        <?php elseif($subtab == 'confirmed'): ?>
                                            <button class="btn-action btn-approve" style="background: #e3f2fd; color: #1976d2;" onclick="updateStatus(<?php echo $row['id']; ?>, 'Completed')">Mark Done</button>
                                            <button class="btn-action btn-defer" onclick="updateStatus(<?php echo $row['id']; ?>, 'Deferred')">Move to Deferred</button>
                                        <?php elseif($subtab == 'deferred'): ?>
                                            <button class="btn-action btn-approve" onclick="updateStatus(<?php echo $row['id']; ?>, 'Pending')">Re-evaluate</button>
                                        <?php elseif($subtab == 'completed'): ?>
                                            <span style="color: #2e7d32; font-weight: bold;"><i class="fa-solid fa-circle-check"></i> Finished</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding: 50px; color: #ccc;">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($tab == 'calendar'): ?>
                <div class="cal-header">
                    <h2 style="margin:0; color: #444; font-weight: 600;"><?php echo date('F Y', $first_day_of_month); ?></h2>
                    <div style="display:flex; gap:10px;">
                        <a href="?tab=calendar&month=<?php echo date('m', strtotime('-1 month', $first_day_of_month)); ?>&year=<?php echo date('Y', strtotime('-1 month', $first_day_of_month)); ?>" class="cal-nav-btn"><i class="fa-solid fa-chevron-left"></i></a>
                        <a href="?tab=calendar&month=<?php echo date('m', strtotime('+1 month', $first_day_of_month)); ?>&year=<?php echo date('Y', strtotime('+1 month', $first_day_of_month)); ?>" class="cal-nav-btn"><i class="fa-solid fa-chevron-right"></i></a>
                    </div>
                </div>

                <div class="legend">
                    <span><i class="fa-solid fa-circle" style="color: var(--peter-pink);"></i> Has Appointment</span>
                    <span><i class="fa-solid fa-square" style="color: #ffebee; border: 1px solid #ffcdd2;"></i> Not Available (Day Off)</span>
                </div>

                <div class="calendar-grid">
                    <div class="cal-day-head">SUN</div><div class="cal-day-head">MON</div><div class="cal-day-head">TUE</div>
                    <div class="cal-day-head">WED</div><div class="cal-day-head">THU</div><div class="cal-day-head">FRI</div><div class="cal-day-head">SAT</div>

                    <?php
                    for ($i = 0; $i < $first_day_weekday; $i++) echo '<div class="cal-day empty"></div>';

                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $current_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $is_today = ($current_date == date('Y-m-d')) ? 'today' : '';
                        $is_disabled = in_array($current_date, $disabled_dates);
                        $has_apt = in_array($current_date, $booked_dates);
                        
                        $day_class = $is_today . ($is_disabled ? ' disabled-day' : '');
                        $has_apt_val = $has_apt ? 'true' : 'false';

                        echo "<div class='cal-day $day_class' onclick=\"manageDate('$current_date', $has_apt_val)\">";
                        echo "<span class='day-num'>$day</span>";
                        echo "<div class='status-icons'>";
                        if ($has_apt) echo "<div class='apt-dot'></div>";
                        if ($is_disabled) echo "<span class='off-label'>CLOSED</span>";
                        echo "</div></div>";
                    }
                    ?>
                </div>

            <?php elseif ($tab == 'search'): ?>
                <div style="text-align:center; padding:50px; color:#aaa;">Search Feature Coming Soon...</div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Main function for Calendar Interaction
      // Updated function: Tinanggal na ang "Set as Available" button
    function manageDate(date, hasApt) {
        Swal.fire({
            title: 'Date Management',
            html: `Selected Date: <b>${date}</b>`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Toggle Day-Off', // Ito na ang primary button
            cancelButtonText: 'Close',
            showDenyButton: hasApt, // Ipapakita lang ang "View Appointments" kung may schedule
            denyButtonText: 'View Appointments',
            confirmButtonColor: '#444', // Dark color para sa toggle
            denyButtonColor: '#ff6b9d', // Pink color para sa view appointments
            cancelButtonColor: '#aaa'
        }).then((result) => {
            if (result.isConfirmed) {
                // Pag clinick ang Toggle Day-Off
                toggleDayOff(date);
            } else if (result.isDenied) {
                // Pag clinick ang View Appointments (lalabas lang ito kung hasApt = true)
                window.location.href = `?tab=requests&sub=confirmed&date=${date}`;
            }
        });
    }

    function toggleDayOff(date) {
        fetch('../action/toggle_day_off.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `date=${date}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                location.reload();
            } else {
                Swal.fire('Error', 'Could not update status', 'error');
            }
        });
    }

        function updateStatus(id, newStatus) {
            Swal.fire({
                title: 'Confirm Action',
                text: `Mark this as ${newStatus}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ff6b9d',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../action/update_apt_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${id}&status=${newStatus}`
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data.trim() === 'success') {
                            location.reload();
                        } else {
                            Swal.fire('Error', data, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
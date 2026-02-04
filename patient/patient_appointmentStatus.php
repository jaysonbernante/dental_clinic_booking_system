<?php
session_start();
$pageTitle = "patient appointmentStatus";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';
$user_id = $_SESSION['user_id'];

// Fetch Profile Data
$user_query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$user_result = mysqli_query($connect, $user_query);
$user = mysqli_fetch_assoc($user_result);

/**
 * Function para kunin ang appointments kasama ang pangalan ng Dentist 
 * at ang 'created_at' date galing sa database
 */
function getAppointments($connect, $user_id, $status_array)
{
    $statuses = "'" . implode("','", $status_array) . "'";
    $query = "SELECT a.*, u.last_name as doctor_name 
              FROM appointments a 
              LEFT JOIN users_management u ON a.dentist_id = u.id 
              WHERE a.user_id = '$user_id' AND a.status IN ($statuses) 
              ORDER BY a.created_at DESC";
    return mysqli_query($connect, $query);
}

$upcoming_result = getAppointments($connect, $user_id, ['Pending', 'Approved']);
$previous_result = getAppointments($connect, $user_id, ['Completed']);
$deferred_result = getAppointments($connect, $user_id, ['Deferred', 'Cancelled']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Status - Peter Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --peter-pink: #ff6b9d;
            --peter-bg-light: #fff5f8;
            --status-green: #00c853;
            --status-blue: #2196f3;
            --status-orange: #ff9800;
            --text-grey: #888;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #FDEDEE;
            color: #444;
            height: 100vh;
            overflow: hidden; /* Pinipigilan ang scroll sa buong page */
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            height: calc(100vh - 120px); /* Adjust base sa header height */
            align-items: start;
        }

        /* Dito lang lalabas ang scrollbar */
        main {
            height: 100%;
            overflow-y: auto;
            padding-right: 15px;
        }

        main::-webkit-scrollbar {
            width: 6px;
        }

        main::-webkit-scrollbar-track {
            background: transparent;
        }

        main::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 10px;
        }

        main::-webkit-scrollbar-thumb:hover {
            background: var(--peter-pink);
        }

        aside {
            position: sticky;
            top: 0;
        }

        .appointment-main-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .main-title {
            color: var(--peter-pink);
            margin: 0;
            font-size: 26px;
            font-weight: 600;
        }

        .sub-title {
            font-size: 16px;
            color: var(--text-grey);
            margin: 25px 0 15px;
            font-weight: 400;
        }

        .apt-row {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border-left: 12px solid #ddd;
            cursor: pointer;
            transition: 0.3s;
        }

        .apt-row:hover {
            transform: scale(1.01);
        }

        .apt-row.Pending { border-left-color: var(--status-orange); }
        .apt-row.Approved { border-left-color: var(--status-green); }
        .apt-row.Completed { border-left-color: var(--status-blue); }
        .apt-row.Cancelled, .apt-row.Deferred { border-left-color: var(--peter-pink); }

        .apt-grid {
            display: grid;
            grid-template-columns: 1.2fr 1.2fr 1fr;
            flex-grow: 1;
            padding-left: 10px;
        }

        /* Column para sa Doctor at Status */
        .doctor-status-col {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .text-main {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            display: block;
        }

        .text-sub {
            font-size: 11px;
            color: #bbb;
            display: block;
        }

        .status-tag {
            font-size: 10px;
            padding: 2px 10px;
            border-radius: 12px;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            display: inline-block;
        }

        .bg-orange { background: var(--status-orange); }
        .bg-green { background: var(--status-green); }
        .bg-blue { background: var(--status-blue); }
        .bg-pink { background: var(--peter-pink); }

        .status-box {
            width: 25px;
            height: 25px;
            background: var(--peter-pink);
            border-radius: 4px;
            margin-left: 20px;
        }

        /* Details Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            width: 450px;
            border-radius: 20px;
            padding: 30px;
            position: relative;
        }

        .modal-header {
            border-bottom: 2px solid var(--peter-bg-light);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: var(--peter-pink);
            margin: 0;
            font-size: 18px;
        }

        .info-group { margin-bottom: 15px; }
        .info-group label { display: block; font-size: 11px; color: #aaa; text-transform: uppercase; }
        .info-group span { font-size: 14px; color: #444; font-weight: 500; }
        .modal-footer { margin-top: 25px; display: flex; gap: 10px; }
        .btn-modal { flex: 1; padding: 12px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; }
        .btn-close { background: #eee; color: #777; }
        .btn-defer { background: #ffebee; color: var(--peter-pink); }

        /* --- SWEETALERT PINK TOAST CUSTOMS --- */
        .swal2-timer-progress-bar {
            background: var(--peter-pink) !important;
        }
        .swal2-popup.swal2-toast {
            border: 1.5px solid #ffd1e1 !important;
            border-radius: 12px !important;
        }
    </style>
</head>

<body>
    <?php include '../component/headerTop_patient.php'; ?>

    <div class="container">
        <aside>
            <?php include '../component/profileCard_patient.php'; ?>
        </aside>

        <?php include '../component/modalPatientSchedule.php'; ?>

        <main>
            <div class="appointment-main-card">
                <h1 class="main-title">Patient's Schedule</h1>

                <h2 class="sub-title">Upcoming Appointments</h2>
                <?php if (mysqli_num_rows($upcoming_result) > 0):
                    while ($row = mysqli_fetch_assoc($upcoming_result)):
                        $status_class = (strtolower($row['status']) == 'pending') ? 'bg-orange' : 'bg-green';
                ?>
                        <div class="apt-row <?php echo $row['status']; ?>" onclick="showDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <div class="apt-grid">
                                <div>
                                    <span class="text-main"><?php echo date('g:i a M d, Y', strtotime($row['appointment_date'] . ' ' . $row['appointment_time'])); ?></span>
                                    <span class="text-sub"><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                                <div>
                                    <span class="text-sub">Preferred Date: <b><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></b></span>
                                    <span class="text-sub">Created Date: <b><?php echo date('M d, Y', strtotime($row['created_at'])); ?></b></span>
                                </div>
                                <div class="doctor-status-col">
                                    <span class="text-main">Dr. <?php echo htmlspecialchars($row['doctor_name'] ?? 'Admin'); ?></span>
                                    <span class="status-tag <?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                                </div>
                            </div>
                            <div class="status-box"></div>
                        </div>
                <?php endwhile;
                else: echo "<p class='text-sub'>No upcoming appointments.</p>";
                endif; ?>

                <h2 class="sub-title">Previous Appointments</h2>
                <?php if (mysqli_num_rows($previous_result) > 0):
                    while ($row = mysqli_fetch_assoc($previous_result)): ?>
                        <div class="apt-row Completed" onclick="showDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <div class="apt-grid">
                                <div>
                                    <span class="text-main"><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></span>
                                    <span class="text-sub"><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                                <div><span class="text-sub">Created: <b><?php echo date('M d, Y', strtotime($row['created_at'])); ?></b></span></div>
                                <div class="doctor-status-col">
                                    <span class="text-main">Dr. <?php echo htmlspecialchars($row['doctor_name'] ?? 'Admin'); ?></span>
                                    <span class="status-tag bg-blue">Completed</span>
                                </div>
                            </div>
                            <div class="status-box"></div>
                        </div>
                <?php endwhile;
                endif; ?>

                <h2 class="sub-title">DEFERRED Appointments</h2>
                <?php if (mysqli_num_rows($deferred_result) > 0):
                    while ($row = mysqli_fetch_assoc($deferred_result)): ?>
                        <div class="apt-row Deferred" onclick="showDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <div class="apt-grid">
                                <div>
                                    <span class="text-main"><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></span>
                                    <span class="text-sub"><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                                <div>
                                    <span class="text-sub">Created: <b><?php echo date('M d, Y', strtotime($row['created_at'])); ?></b></span>
                                    <span class="text-sub">Deferred Date: <b><?php echo date('M d, Y', strtotime($row['updated_at'] ?? $row['created_at'])); ?></b></span>
                                </div>
                                <div class="doctor-status-col">
                                    <span class="text-main">Dr. <?php echo htmlspecialchars($row['doctor_name'] ?? 'Admin'); ?></span>
                                    <span class="status-tag bg-pink"><?php echo $row['status']; ?></span>
                                </div>
                            </div>
                            <div class="status-box"></div>
                        </div>
                <?php endwhile;
                endif; ?>
            </div>
        </main>
    </div>

    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Full Appointment Details</h2>
            </div>
            <div id="modalBody">
                <div class="info-group"><label>Created Date</label><span id="det-created"></span></div>
                <div class="info-group"><label>Appointment Schedule</label><span id="det-date"></span></div>
                <div class="info-group"><label>Dentist</label><span id="det-dentist"></span></div>
                <div class="info-group"><label>Reason for Visit</label><span id="det-reason"></span></div>
                <div class="info-group"><label>Remarks</label><span id="det-remarks"></span></div>
                <div class="info-group"><label>Current Status</label><span id="det-status"></span></div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-defer" id="cancelBtn" onclick="cancelAppointment()">DEFER REQUEST</button>
                <button class="btn-modal btn-close" onclick="closeModal()">CLOSE</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentAptId = null;

        function showDetails(data) {
            currentAptId = data.id;
            let created = new Date(data.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            document.getElementById('det-created').innerText = created;
            document.getElementById('det-date').innerText = data.appointment_date + ' at ' + data.appointment_time;
            document.getElementById('det-dentist').innerText = 'Dr. ' + (data.doctor_name || 'Admin');
            document.getElementById('det-reason').innerText = data.reason || 'None';
            document.getElementById('det-remarks').innerText = data.remarks || 'No remarks provided';
            document.getElementById('det-status').innerText = data.status;

            const cancelBtn = document.getElementById('cancelBtn');
            if (['Completed', 'Cancelled', 'Deferred'].includes(data.status)) {
                cancelBtn.style.display = 'none';
            } else {
                cancelBtn.style.display = 'block';
            }
            document.getElementById('detailsModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        function cancelAppointment() {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to defer this appointment request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff6b9d',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, Defer it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../action/update_apt_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${currentAptId}&status=Deferred`
                    })
                    .then(response => response.text())
                    .then(res => {
                        if (res.trim() === 'success') {
                            // --- ADDED TOASTED NOTIFICATION ---
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'Status Updated!',
                                text: 'Appointment has been deferred.',
                                iconColor: '#ff6b9d'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', 'Failed to update.', 'error');
                        }
                    });
                }
            })
              closeModal();
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('detailsModal')) closeModal();
        }
    </script>
</body>
</html>
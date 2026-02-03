<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// 2. Include Connection
include '../action/connection.php';

// Fix connection variable if necessary
if (!isset($conn)) {
    if (isset($connect)) { $conn = $connect; }
    elseif (isset($con)) { $conn = $con; }
    else { die("Database connection failed."); }
}

$user_id = $_SESSION['user_id'];

// 3. FETCH USER DATA (This is what was missing!)
$user_query = "SELECT * FROM users_management WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_data = mysqli_fetch_assoc($user_result);
} else {
    // Fallback if user not found
    $user_data = ['first_name' => 'User', 'last_name' => 'Name', 'email' => ''];
}

// 4. Define variables used in the component
$profile_img = !empty($user_data['profile_pix']) 
               ? "../uploads/profile_pics/" . $user_data['profile_pix'] 
               : "https://via.placeholder.com/150";

// Logic for active tab
$tab = $_GET['tab'] ?? 'requests';
$subtab = $_GET['sub'] ?? 'pending';
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
        :root {
            --peter-pink: #ff6b9d;
            --sidebar-width: 260px;
            --bg-light: #fff5f8;
            --transition: all 0.3s ease;
        }

        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: #f8f9fa; display: flex; }

        /* --- Sidebar --- */
        .sidebar { width: var(--sidebar-width); background-color: var(--peter-pink); height: 100vh; color: white; position: fixed; left: 0; top: 0; z-index: 1100; transition: var(--transition); padding-top: 30px; }
        .sidebar-header { text-align: center; margin-bottom: 30px; }
        .logo-circle { width: 80px; height: 80px; background: white; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 3px solid rgba(255,255,255,0.3); }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        
        .nav-menu { list-style: none; padding: 0; }
        .nav-item { padding: 15px 25px; display: flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 14px; transition: var(--transition); }
        .nav-item:hover, .nav-item.active { background: rgba(255, 255, 255, 0.2); border-left: 4px solid white; }

        /* --- Main Content --- */
        .main-container { margin-left: var(--sidebar-width); flex: 1; padding: 0 20px 20px; width: calc(100% - var(--sidebar-width)); }


        /* --- Navigation Tabs --- */
        .tabs-nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 8px 20px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; background: white; color: var(--peter-pink); border: 1px solid #eee; transition: 0.3s; }
        .tab-btn.active { background: var(--peter-pink); color: white; border-color: var(--peter-pink); }

        .sub-tabs { display: flex; gap: 20px; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        .sub-btn { padding: 10px 5px; text-decoration: none; font-size: 14px; color: #777; border-bottom: 2px solid transparent; }
        .sub-btn.active { color: var(--peter-pink); border-bottom: 2px solid var(--peter-pink); font-weight: 700; }

        /* --- Content Card --- */
        .content-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); min-height: 400px; }

        /* --- Calendar Logic Style --- */
        .calendar-wrapper { width: 100%; max-width: 800px; margin: 0 auto; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-header button { background: none; border: 1px solid #eee; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; color: var(--peter-pink); font-size: 18px; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .calendar-header button:hover { background: var(--bg-light); }
        .calendar-days, .calendar-dates { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; }
        .calendar-days div { font-weight: bold; padding: 10px 0; color: #aaa; font-size: 12px; }
        .calendar-dates div { padding: 20px 0; border-radius: 12px; cursor: pointer; transition: 0.2s; color: #555; font-size: 14px; border: 1px solid transparent; }
        .calendar-dates div:hover { background: var(--bg-light); color: var(--peter-pink); }
        .today { background: var(--peter-pink) !important; color: white !important; font-weight: bold; box-shadow: 0 4px 10px rgba(255, 107, 157, 0.3); }

        /* --- Table Styling --- */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; font-size: 12px; color: #aaa; text-transform: uppercase; padding: 12px; border-bottom: 1px solid #eee; }
        td { padding: 15px 12px; font-size: 14px; border-bottom: 1px solid #fafafa; }

        .search-container { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-input { flex: 1; padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; outline: none; }

        @media (max-width: 992px) {
            .sidebar { left: -100%; }
            .sidebar.active { left: 0; }
            .main-container { margin-left: 0; }
            .burger-btn { display: block; }
        }
    </style>
</head>
<body>

    <?php
         
         include '../component/sideBar_dentist.php'; 
         ?>

    <div class="main-container">
         <?php
         $pageTitle = "Appointments";
         include '../component/headerTop_dentist.php'; 
         ?>

        <div class="tabs-nav">
            <a href="?tab=requests" class="tab-btn <?php echo $tab == 'requests' ? 'active' : ''; ?>">Requests</a>
            <a href="?tab=calendar" class="tab-btn <?php echo $tab == 'calendar' ? 'active' : ''; ?>">Calendar</a>
            <a href="?tab=search" class="tab-btn <?php echo $tab == 'search' ? 'active' : ''; ?>">Search</a>
        </div>

        <div class="content-card">
            
            <?php if ($tab == 'requests'): ?>
                <div class="sub-tabs">
                    <a href="?tab=requests&sub=pending" class="sub-btn <?php echo $subtab == 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?tab=requests&sub=deferred" class="sub-btn <?php echo $subtab == 'deferred' ? 'active' : ''; ?>">Deferred</a>
                </div>
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
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 50px; color: #ccc;">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 30px; display:block; margin-bottom:10px;"></i>
                                No <?php echo $subtab; ?> requests.
                            </td>
                        </tr>
                    </tbody>
                </table>

            <?php elseif ($tab == 'calendar'): ?>
                <div class="calendar-wrapper">
                    <div class="calendar-header">
                        <button id="prev"><i class="fa-solid fa-chevron-left"></i></button>
                        <h3 id="monthYear" style="margin:0; color:var(--peter-pink);"></h3>
                        <button id="next"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>

                    <div class="calendar-days">
                        <div>SUN</div><div>MON</div><div>TUE</div>
                        <div>WED</div><div>THU</div><div>FRI</div><div>SAT</div>
                    </div>

                    <div class="calendar-dates" id="dates"></div>
                </div>

            <?php elseif ($tab == 'search'): ?>
                <div class="search-container">
                    <input type="text" class="search-input" id="apptSearch" placeholder="Search patient name..." onkeyup="searchAppt()">
                    <button style="background: var(--peter-pink); color:white; border:none; padding:10px 20px; border-radius:8px;">Search</button>
                </div>
                <table id="apptTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#001</td>
                            <td><strong>Patient 1</strong></td>
                            <td>Feb 05, 2026</td>
                            <td>09:00 AM</td>
                            <td><span style="color: var(--peter-pink);">Confirmed</span></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Calendar Logic (Only runs if the calendar tab is active)
        if (document.getElementById("dates")) {
            const monthYear = document.getElementById("monthYear");
            const dates = document.getElementById("dates");
            const prev = document.getElementById("prev");
            const next = document.getElementById("next");

            let currentDate = new Date();

            function renderCalendar() {
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();
                const firstDay = new Date(year, month, 1).getDay();
                const lastDate = new Date(year, month + 1, 0).getDate();
                const today = new Date();

                dates.innerHTML = "";
                monthYear.textContent = currentDate.toLocaleDateString("en-US", {
                    month: "long",
                    year: "numeric"
                });

                for (let i = 0; i < firstDay; i++) {
                    dates.innerHTML += `<div></div>`;
                }

                for (let day = 1; day <= lastDate; day++) {
                    let isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();
                    dates.innerHTML += `<div class="${isToday ? 'today' : ''}">${day}</div>`;
                }
            }

            prev.addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });

            next.addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });

            renderCalendar();
        }

        function searchAppt() {
            let input = document.getElementById("apptSearch");
            let filter = input.value.toUpperCase();
            let table = document.getElementById("apptTable");
            let tr = table.getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }
    </script>
</body>
</html>
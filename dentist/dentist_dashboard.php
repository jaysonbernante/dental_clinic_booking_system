<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// 2. Include Connection
include '../action/connection.php';

/**
 * 3. Connection Variable Fix
 */
if (!isset($conn)) {
    if (isset($connect)) { $conn = $connect; }
    elseif (isset($con)) { $conn = $con; }
    elseif (isset($db)) { $conn = $db; }
    else {
        die("Critical Error: Database connection variable not found.");
    }
}

$user_id = $_SESSION['user_id'];

// 4. Fetch User Data
$user_query = "SELECT * FROM users_management WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_data = mysqli_fetch_assoc($user_result);
} else {
    $user_data = [
        'first_name' => 'User',
        'last_name' => 'Name',
        'email' => 'Not set',
        'profile_pix' => '',
        'birthday' => '',
        'mobile_number' => '',
        'gender' => 'Male',
        'address' => ''
    ];
}

// 5. Counters
$total_patients = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users_management WHERE role = 'patient'"); 
if($res) { $row = mysqli_fetch_assoc($res); $total_patients = $row['total']; }

$new_patients = 3; 
$pending_requests = 1;

$admin_full_name = htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']);
$profile_img = !empty($user_data['profile_pix']) ? "../uploads/profile_pics/" . $user_data['profile_pix'] : "https://via.placeholder.com/150";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peter Dental - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --peter-pink: #ff6b9d;
            --peter-blue: #0081C9;
            --sidebar-width: 260px;
            --bg-light: #fff5f8;
            --transition: all 0.3s ease;
        }

        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--bg-light); display: flex; overflow-x: hidden; }

        
        /* --- Main Content --- */
        .main-container { margin-left: var(--sidebar-width); flex: 1; padding: 0 20px 20px; width: calc(100% - var(--sidebar-width)); }

      
        /* --- Dashboard Stats --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .section-title { font-size: 14px; font-weight: 700; color: #444; margin: 25px 0 15px; text-transform: uppercase; }
        .data-row { background: white; padding: 15px 20px; border-radius: 10px; display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }

     
       
    </style>
</head>
<body>

    <?php
         
         include '../component/sideBar_dentist.php'; 
         ?>
  
    <div class="main-container">
         <?php
         $pageTitle = "Dashboard";
         include '../component/headerTop_dentist.php'; ?>
       
        <div style="margin-top:25px;">
            <h2 style="margin:0;">Welcome Back, Doc <?php echo htmlspecialchars($user_data['first_name']); ?></h2>
            <p style="color:#888; font-size:13px;"><?php echo date("l, F j, Y"); ?></p>
        </div>

        <div class="section-title">Overview</div>
        <div class="stats-grid">
            <div class="stat-card"><h3><?php echo $total_patients; ?></h3><p>Total Patients</p></div>
            <div class="stat-card"><h3><?php echo $new_patients; ?></h3><p>New Patients</p></div>
            <div class="stat-card"><h3><?php echo $pending_requests; ?></h3><p>Pending Requests</p></div>
        </div>

        <div class="section-title">Appointments Today</div>
        <div class="data-row">
            <strong>06:30 PM</strong>
            <span>Patient 1</span>
            <span style="color:var(--peter-pink)">Checkup</span>
        </div>
    </div>

</body>
</html>
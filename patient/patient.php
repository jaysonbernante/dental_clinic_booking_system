<?php
// 1. Fix Session Notice: Only start if one isn't active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Security Check: Redirect to login if session is empty
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../action/connection.php'; 

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$role = $_SESSION['user_type'] ?? 'patient'; 

// 3. Fetch User Profile Details (This uses your existing 'users' table)
$user_query = $connect->prepare("SELECT name, email FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

/** * NOTE: The following variables are set to null because the tables 
 * 'medical_history', 'appointments', and 'dentists' do not exist yet.
 * This prevents the "Fatal error: Table doesn't exist" crash.
 */
$history = null; 
$appointment = null; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peter Dental - Patient Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --peter-pink: #ff6b9d;
            --light-pink: #fff0f5;
            --text-dark: #333;
            --shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fcfcfc;
            margin: 0;
            color: var(--text-dark);
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 8%;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .logo { color: var(--peter-pink); font-weight: 700; font-size: 24px; text-decoration: none; }
        
        .nav-links a {
            text-decoration: none;
            color: var(--peter-pink);
            margin-left: 30px;
            font-weight: 500;
            font-size: 14px;
        }

        .btn-logout {
            background: var(--peter-pink);
            color: white !important;
            padding: 8px 20px;
            border-radius: 20px;
        }

        /* Main Layout */
        .main-content {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 40px;
            padding: 40px 8%;
            max-width: 1400px;
            margin: auto;
        }

        /* Profile Sidebar */
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            text-align: center;
            padding-bottom: 20px;
        }

        .card-header-pink {
            background: var(--peter-pink);
            height: 80px;
            position: relative;
            margin-bottom: 50px;
        }

        .profile-img {
            width: 90px;
            height: 90px;
            background: #eee;
            border-radius: 50%;
            position: absolute;
            bottom: -45px;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid white;
            display: flex;
            align-items: center; justify-content: center;
            font-size: 30px; color: #ccc;
        }

        .patient-info-box {
            background: var(--light-pink);
            border-radius: 15px;
            margin-top: 20px;
            padding: 25px;
        }

        /* Content Card */
        .history-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: var(--shadow);
        }

        .history-header h2 { color: var(--peter-pink); margin-top: 0; }

        .info-row {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
        }

        .empty-notice {
            color: #999;
            font-style: italic;
            padding: 20px 0;
        }

        @media (max-width: 900px) {
            .main-content { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">Peter Dental</a>
        <div class="nav-links">
            <a href="#">Dashboard</a>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="sidebar">
            <div class="profile-card">
                <div class="card-header-pink">
                    <div class="profile-img"><i class="fa-solid fa-user"></i></div>
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($user_data['name'] ?? 'User'); ?></h3>
                    <p>Patient ID: #<?php echo $user_id; ?></p>
                </div>
            </div>

            <div class="patient-info-box">
                <h4 style="margin-top:0; font-size:14px; color: #ff85a2;">Upcoming Appointment</h4>
                <div style="font-size: 13px; color: #777;">
                    <?php if ($appointment): ?>
                        <p><i class="fa-calendar"></i> <?php echo $appointment['appointment_date']; ?></p>
                    <?php else: ?>
                        <p>No upcoming visits found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="history-card">
            <div class="history-header">
                <h2>Account Information</h2>
            </div>
            
            <div class="info-row">
                <span><strong>Full Name:</strong></span>
                <span><?php echo htmlspecialchars($user_data['name'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span><strong>Email Address:</strong></span>
                <span><?php echo htmlspecialchars($user_data['email'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span><strong>Account Type:</strong></span>
                <span><?php echo ucfirst($role); ?></span>
            </div>

            <br><br>
            <h3>Medical History</h3>
            <p class="empty-notice">Medical history records are currently unavailable as the system is being updated.</p>
        </div>
    </main>

</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dentist Dashboard | Peter Dental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/patient.css">  <!-- Adjust path if needed -->
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #fff;
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }
        .sidebar h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            text-decoration: none;
            color: #555;
            font-size: 16px;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .sidebar ul li a:hover {
            background-color: #f0f0f0;
            color: #333;
        }
        .sidebar ul li a i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3><i class="fa-solid fa-tooth"></i> Dentist Panel</h3>
        <ul>
            <li>admin</li>
            <li><a href="../dentist/admin_dashboard.php"><i class="fa-solid fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="../dentist/manage_appointments.php"><i class="fa-solid fa-calendar-alt"></i> Manage Appointments</a></li>
            <li><a href="../dentist/manage_services.php"><i class="fa-solid fa-calendar-check"></i> Manage Services</a></li>
             <li><a href="../dentist/manage_staff.php"><i class="fa-solid fa-calendar-check"></i> Manage Staff Accounts</a></li>
              <li><a href="../dentist/reports.php"><i class="fa-solid fa-calendar-check"></i> Reports</a></li>
               <li>Dentist</li>
              <li><a href="../dentist/dentist_dashboard.php"><i class="fa-solid fa-calendar-check"></i> manage_staff</a></li>
                <li><a href="../dentist/dentist_schedule.php"><i class="fa-solid fa-calendar-check"></i> reports</a></li>
                
            <li><a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    

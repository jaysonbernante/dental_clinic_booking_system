<?php
session_start();

// Simple login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../action/connection.php'; // <-- your database connection

$user_id = $_SESSION['user_id'];

// Fetch next upcoming appointment for this patient (updated to show all services)
$stmt = $connect->prepare("
    SELECT a.appointment_date, a.appointment_time, GROUP_CONCAT(s.service_name SEPARATOR ', ') AS service_names, d.name AS dentist_name, a.status
    FROM appointments a
    JOIN dentists d ON a.dentist_id = d.id
    JOIN appointment_services asv ON a.id = asv.appointment_id
    JOIN services s ON asv.service_id = s.id
    WHERE a.user_id = ? AND a.appointment_date >= CURDATE()
    GROUP BY a.id
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peter Dental - Gentle Care for Your Smile</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Existing CSS -->
    <link rel="stylesheet" href="../assets/css/patient.css">

    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
        }

        main.container {
            padding-top: 140px;
            max-width: 900px;
            margin: auto;
        }

        main h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
        }

        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .card {
            background: white;
            flex: 1 1 200px;
            padding: 25px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card h4 {
            font-size: 18px;
            font-weight: 600;
            color: #555;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 16px;
            color: #777;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .welcome-msg {
            font-size: 20px;
            margin-bottom: 30px;
            color: #333;
        }

        @media (max-width: 600px) {
            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
  
  <?php include '../component/patient_navbar.php'; ?>

<main class="container">
    <div class="welcome-msg">
        Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋
    </div>

    <div class="stats">
        <div class="card">
            <h4><i class="fa-solid fa-calendar-check"></i> Upcoming Appointment</h4>
            <?php if ($appointment): ?>
                <p>
                    <?php 
                        echo date("F j, Y", strtotime($appointment['appointment_date'])) . " at " . date("h:i A", strtotime($appointment['appointment_time']));
                        echo "<br>Services: " . htmlspecialchars($appointment['service_names']);  // Now shows all services
                        echo "<br>Dentist: " . htmlspecialchars($appointment['dentist_name']);
                    ?>
                </p>
            <?php else: ?>
                <p>No upcoming appointment</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h4><i class="fa-solid fa-info-circle"></i> Appointment Status</h4>
            <?php if ($appointment): ?>
                <p><?php echo ucfirst($appointment['status']); ?></p>
            <?php else: ?>
                <p>—</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Role display
  const role = "user";
  document.querySelectorAll('.admin-menu').forEach(item => {
    item.style.display = role === "admin" ? "block" : "none";
  });
  document.querySelectorAll('.patient-menu').forEach(item => {
    item.style.display = role === "user" ? "block" : "none";
  });
</script>

</body>
</html>
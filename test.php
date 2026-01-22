<?php
// Include your DB connection
include 'action/connection.php';  // Adjust path if needed

// Function to insert dentist
function insertDentist($name, $specialization, $status, $email, $password) {
    global $connect;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $connect->prepare("INSERT INTO dentists (name, specialization, status, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $specialization, $status, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Successfully inserted: $name ($specialization)</p>";
    } else {
        echo "<p style='color: red;'>Error inserting $name: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Create Dentist Accounts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 500px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Test: Creating Dentist Accounts</h2>
        <?php
        // Insert sample dentists
        insertDentist('Dr. Smith', 'General Dentistry', 'Available', 'dentist@peterdental.com', 'dentist');
        insertDentist('Dr. Johnson', 'Orthodontics', 'Available', 'dentist2@peterdental.com', 'dentist2');

        // Close connection
        $connect->close();
        ?>
        <p><strong>Note:</strong> Delete this file after testing for security.</p>
    </div>
</body>
</html>
<?php
include 'connection.php';

$date = $_GET['date'] ?? '';
$booked_slots = [];

if ($date) {
    // Kunin ang lahat ng appointment_time sa napiling date
    $query = "SELECT appointment_time FROM appointments WHERE appointment_date = '$date' AND status != 'Cancelled'";
    $result = mysqli_query($connect, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        // I-format ang time para tumugma sa array (H:i)
        $booked_slots[] = date("H:i", strtotime($row['appointment_time']));
    }
}

echo json_encode($booked_slots);
?>
<?php
include 'connection.php';
if (!isset($conn)) { $conn = $connect ?? $con; }

$date = $_GET['date'] ?? '';
$response = [];

if ($date) {
    // 1. Check kung ang araw na ito ay naka Day-Off/Holiday
    $check_disabled = mysqli_query($conn, "SELECT id FROM disabled_dates WHERE disabled_date = '$date'");
    
    if (mysqli_num_rows($check_disabled) > 0) {
        // Kung naka Day-off, mag-send ng flag na "CLOSED"
        echo json_encode(['status' => 'disabled', 'message' => 'Clinic is closed on this day.']);
        exit;
    }

    // 2. Kung hindi Day-off, kunin ang mga booked time slots
    $query = "SELECT appointment_time FROM appointments WHERE appointment_date = '$date' AND status != 'Cancelled'";
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        // I-format ang time para tumugma sa value ng select options (e.g., "08:00")
        $response[] = date('H:i', strtotime($row['appointment_time']));
    }
}

echo json_encode(['status' => 'available', 'booked' => $response]);
?>
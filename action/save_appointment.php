<?php
session_start();
include 'connection.php';

// I-set ang header para JSON ang ibalik sa JavaScript
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kunin ang User ID mula sa session
    $user_id = $_SESSION['user_id'];
    
    // Kunin ang mga data mula sa POST
    $dentist_id = mysqli_real_escape_string($connect, $_POST['dentist_id']);
    $date       = mysqli_real_escape_string($connect, $_POST['schedule_date']);
    $time       = mysqli_real_escape_string($connect, $_POST['schedule_time']);
    $address    = mysqli_real_escape_string($connect, $_POST['address']);
    $birthday   = mysqli_real_escape_string($connect, $_POST['birthday']);
    $gender     = mysqli_real_escape_string($connect, $_POST['gender']);
    $contact    = mysqli_real_escape_string($connect, $_POST['contact']);
    $reason     = mysqli_real_escape_string($connect, $_POST['reason']);
    $remarks    = mysqli_real_escape_string($connect, $_POST['remark']);

    // SQL Query (Tugma sa bagong DROP/CREATE na table structure)
    $query = "INSERT INTO appointments 
              (user_id, dentist_id, appointment_date, appointment_time, address, birthday, gender, contact_number, reason, remarks, status) 
              VALUES 
              ('$user_id', '$dentist_id', '$date', '$time', '$address', '$birthday', '$gender', '$contact', '$reason', '$remarks', 'Pending')";

    if (mysqli_query($connect, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment saved successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($connect)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
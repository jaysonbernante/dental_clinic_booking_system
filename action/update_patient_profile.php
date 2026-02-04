<?php
// Start session to use for the security check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connection.php';

// Force response to be JSON
header('Content-Type: application/json');

// Error logging - if there's a crash, this helps
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't let PHP echo raw errors; we want JSON

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $id = intval($_POST['user_id']);
    
    // Security check: Match session ID to the requested update ID
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $id) {
        throw new Exception("Unauthorized access.");
    }

    // Sanitize inputs
    $name     = mysqli_real_escape_string($connect, $_POST['name']);
    $email    = mysqli_real_escape_string($connect, $_POST['email']);
    $contact  = mysqli_real_escape_string($connect, $_POST['contact']);
    $gender   = mysqli_real_escape_string($connect, $_POST['gender']);
    $birthday = mysqli_real_escape_string($connect, $_POST['birthday']);
    $address  = mysqli_real_escape_string($connect, $_POST['address']);

    // Check if birthday is empty string, set to NULL if database allows
    $bdayValue = empty($birthday) ? "NULL" : "'$birthday'";

    // Update query - Note: removed email from update if you don't want patients changing their login email
    $sql = "UPDATE users SET 
            name = '$name', 
            email = '$email',
            contact = '$contact', 
            gender = '$gender', 
            birthday = $bdayValue, 
            address = '$address' 
            WHERE id = $id";

    if (mysqli_query($connect, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception(mysqli_error($connect));
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
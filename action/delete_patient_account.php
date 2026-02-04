<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = mysqli_real_escape_string($connect, $_POST['user_id']);

    // Optional: Safety check to ensure the user is deleting THEIR OWN account
    if ($_SESSION['user_id'] != $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
        exit();
    }

    // Delete related records first (if you don't have ON DELETE CASCADE set in your DB)
    // mysqli_query($connect, "DELETE FROM medical_answers WHERE user_id = '$user_id'");
    // mysqli_query($connect, "DELETE FROM appointments WHERE user_id = '$user_id'");

    $query = "DELETE FROM users WHERE id = '$user_id'";

    if (mysqli_query($connect, $query)) {
        session_destroy(); // Log them out since the account is gone
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }
}
?>
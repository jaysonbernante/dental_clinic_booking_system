<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // I-update ang status ng appointment
    $query = "UPDATE appointments SET status = '$status' WHERE id = '$id'";
    
    if (mysqli_query($connect, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
<?php
include 'connection.php';

if (isset($_POST['status'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Gawing 1 ang is_read para sa status na clinick
    $query = "UPDATE appointments SET is_read = 1 WHERE status = '$status' AND is_read = 0";
    
    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
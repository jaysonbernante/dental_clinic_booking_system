<?php
include 'connection.php';
if (!isset($conn)) { $conn = $connect ?? $con; }

if (isset($_POST['date'])) {
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    // Check kung existing na
    $check = mysqli_query($conn, "SELECT * FROM disabled_dates WHERE disabled_date = '$date'");
    
    if (mysqli_num_rows($check) > 0) {
        // Kung nandun na, burahin (Gawing Available uli)
        $query = "DELETE FROM disabled_dates WHERE disabled_date = '$date'";
    } else {
        // Kung wala pa, i-add (Gawing Day-off)
        $query = "INSERT INTO disabled_dates (disabled_date, reason) VALUES ('$date', 'Admin Set Day-Off')";
    }

    if (mysqli_query($conn, $query)) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
}
?>
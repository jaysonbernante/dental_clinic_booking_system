<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbClinic"; // <-- correct database name

// Create connection using mysqli
$connect = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
?>

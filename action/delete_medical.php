<?php
include 'connection.php';
$id = $_GET['id'];

// We only delete the HISTORY, not the user account
$sql = "DELETE FROM medical_history WHERE user_id = $id";

if ($connect->query($sql)) {
    header("Location: ../patient/dentist_Medical_Records.php?deleted=1");
}
?>
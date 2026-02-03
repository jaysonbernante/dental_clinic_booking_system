<?php
// Turn off error reporting to screen so it doesn't break the JSON response
error_reporting(0); 
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id'])) {
    $user_id = intval($_POST['patient_id']);
    $answers = $_POST['answers'] ?? [];

    $success = true;
    foreach ($answers as $q_id => $answer) {
        $q_id = intval($q_id);
        $answer = mysqli_real_escape_string($connect, $answer);
        
        $sql = "INSERT INTO medical_answers (user_id, question_id, answer) 
                VALUES ($user_id, $q_id, '$answer') 
                ON DUPLICATE KEY UPDATE answer = '$answer'";
        
        if (!mysqli_query($connect, $sql)) {
            $success = false;
            $error_msg = mysqli_error($connect);
            break;
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $error_msg]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>
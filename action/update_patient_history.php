<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $success = true;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'q_') === 0) {
            $question_id = str_replace('q_', '', $key);
            $answer = mysqli_real_escape_string($connect, $value);

            // Check if answer already exists
            $check = mysqli_query($connect, "SELECT id FROM medical_answers WHERE user_id = '$user_id' AND question_id = '$question_id'");
            
            if (mysqli_num_rows($check) > 0) {
                $query = "UPDATE medical_answers SET answer = '$answer' WHERE user_id = '$user_id' AND question_id = '$question_id'";
            } else {
                $query = "INSERT INTO medical_answers (user_id, question_id, answer) VALUES ('$user_id', '$question_id', '$answer')";
            }

            if (!mysqli_query($connect, $query)) {
                $success = false;
            }
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>
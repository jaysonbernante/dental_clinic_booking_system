<?php
include 'connection.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = intval($_POST['patient_id']);
    $answers = $_POST['answers'] ?? [];

    $success = true;
    foreach ($answers as $q_id => $val) {
        // targets the unique pair of user_id and question_id
        $stmt = $connect->prepare("INSERT INTO medical_answers (user_id, question_id, answer) 
                                   VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE answer = ?");
        $stmt->bind_param("iiss", $patient_id, $q_id, $val, $val);
        if (!$stmt->execute()) {
            $success = false;
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Answers Updated Successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update some records.']);
    }
    exit();
}
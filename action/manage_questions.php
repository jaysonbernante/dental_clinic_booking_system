<?php
include 'connection.php';
header('Content-Type: application/json'); // Tell the browser we are sending JSON

$response = ['status' => 'error', 'message' => 'Something went wrong'];

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $connect->prepare("DELETE FROM medical_questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'deleted', 'message' => 'Question removed successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete']);
    }
    exit();
}

// Handle Add & Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action_type'];
    $text = trim($_POST['question_text']);
    $id = $_POST['q_id'];

    // --- DUPLICATE VERIFICATION ---
    if ($action == "add") {
        $check = $connect->prepare("SELECT id FROM medical_questions WHERE question_text = ?");
        $check->bind_param("s", $text);
    } else {
        $check = $connect->prepare("SELECT id FROM medical_questions WHERE question_text = ? AND id != ?");
        $check->bind_param("si", $text, $id);
    }
    
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'duplicate', 'message' => 'This question already exists!']);
        exit();
    }

    // --- SAVE LOGIC ---
    if ($action == "add") {
        $stmt = $connect->prepare("INSERT INTO medical_questions (question_text) VALUES (?)");
        $stmt->bind_param("s", $text);
        $final_status = 'success';
    } else if ($action == "update") {
        $stmt = $connect->prepare("UPDATE medical_questions SET question_text = ? WHERE id = ?");
        $stmt->bind_param("si", $text, $id);
        $final_status = 'updated';
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => $final_status, 'message' => 'Action completed successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    exit();
}
?>
<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    
    // Collect radio values
    $fields = ['good_health', 'under_medical_treatment', 'serious_illness_operation', 'hospitalized', 'taking_medication', 'use_tobacco', 'drink_alcohol', 'use_dangerous_drugs', 'allergic_to_anything'];
    $values = [];
    foreach ($fields as $f) { $values[$f] = $_POST[$f] ?? 'No'; }

    // Collect checkboxes
    $conditions = isset($_POST['conditions']) ? implode(', ', $_POST['conditions']) : '';

    // Check if record exists
    $check = $connect->query("SELECT user_id FROM medical_history WHERE user_id = $user_id");

    if ($check->num_rows > 0) {
        // Update
        $sql = "UPDATE medical_history SET 
                good_health='{$values['good_health']}', 
                under_medical_treatment='{$values['under_medical_treatment']}', 
                serious_illness_operation='{$values['serious_illness_operation']}', 
                conditions='$conditions' 
                WHERE user_id = $user_id";
    } else {
        // Insert new
        $sql = "INSERT INTO medical_history (user_id, good_health, under_medical_treatment, serious_illness_operation, conditions) 
                VALUES ($user_id, '{$values['good_health']}', '{$values['under_medical_treatment']}', '{$values['serious_illness_operation']}', '$conditions')";
    }

    if ($connect->query($sql)) {
        header("Location: ../patient/dentist_Medical_Records.php?success=1");
    }
}
?>
<?php
session_start();
include 'connection.php'; 

// --- Smart Connection Check ---
// This ensures that even if your connection.php uses a different name, the script won't crash.
if (!isset($conn)) {
    if (isset($connect)) { $conn = $connect; }
    elseif (isset($con)) { $conn = $con; }
    elseif (isset($db)) { $conn = $db; }
    else {
        die("Fatal Error: Database connection variable not found. Check your action/connection.php file.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // --- HANDLE PROFILE PHOTO UPLOAD ---
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        
        $target_dir = "../uploads/profile_pics/";
        
        // Create folder if it doesn't exist
        if (!file_exists($target_dir)) { 
            mkdir($target_dir, 0777, true); 
        }

        $file_extension = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Move file from temporary location to your uploads folder
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            
            // Update database
            $sql = "UPDATE users_management SET profile_pix = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_filename, $user_id);
            
            if ($stmt->execute()) {
                header("Location: ../dentist/dentist_dashboard.php?upload=success");
            } else {
                echo "Database Error: " . $conn->error;
            }
        } else {
            echo "Error: Could not move the file to $target_dir. Check folder permissions.";
        }
        exit();
    }

    // --- HANDLE ACCOUNT SETTINGS ---
    if (isset($_POST['update_settings'])) {
        $fname = $_POST['first_name'];
        $mname = $_POST['middle_name'];
        $lname = $_POST['last_name'];
        $bday  = $_POST['birthday'];
        $phone = $_POST['mobile_number'];
        $gender = $_POST['gender'];
        $address = $_POST['address'];

        $sql = "UPDATE users_management SET 
                first_name = ?, 
                middle_name = ?, 
                last_name = ?, 
                birthday = ?, 
                mobile_number = ?, 
                gender = ?, 
                address = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $fname, $mname, $lname, $bday, $phone, $gender, $address, $user_id);
        
        if ($stmt->execute()) {
            header("Location: ../dentist/dentist_dashboard.php?update=success");
        } else {
            echo "Update Error: " . $conn->error;
        }
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>
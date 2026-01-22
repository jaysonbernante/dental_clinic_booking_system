<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

$user_id = $_SESSION['user_id'];

// Check if patient has contact number
$stmt_user = $connect->prepare("SELECT contact FROM users WHERE id=?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

if (empty($user['contact'])) {
    $_SESSION['profile_required'] = "Please complete your profile (add contact number) before booking an appointment.";
    header("Location: profile.php");
    exit();
}

// Handle appointment submission
if (isset($_POST['book_appointment'])) {
    $dentist_id = $_POST['dentist_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $service_ids = $_POST['service_ids']; // array of selected services

    if (empty($service_ids)) {
        $error = "Please select at least one dental service.";
    }

    // Check dentist availability (avoid double booking)
    $check_booking = $connect->prepare("SELECT id FROM appointments WHERE dentist_id=? AND appointment_date=? AND appointment_time=?");
    $check_booking->bind_param("iss", $dentist_id, $appointment_date, $appointment_time);
    $check_booking->execute();
    $booking_result = $check_booking->get_result();
    if ($booking_result->num_rows > 0) {
        $error = "This time slot is already booked.";
    }

    if (!isset($error)) {
        // Insert appointment
        $stmt = $connect->prepare("INSERT INTO appointments (user_id, dentist_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("iiss", $user_id, $dentist_id, $appointment_date, $appointment_time);
        $stmt->execute();
        $appointment_id = $stmt->insert_id;

        // Insert selected services
        $stmt_service = $connect->prepare("INSERT INTO appointment_services (appointment_id, service_id) VALUES (?, ?)");
        foreach ($service_ids as $service_id) {
            $stmt_service->bind_param("ii", $appointment_id, $service_id);
            $stmt_service->execute();
        }

        $success = "Appointment booked successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment | Peter Dental</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/patient.css">

<style>
body { font-family: 'Open Sans', sans-serif; background-color: #f8f9fa; }
main.container { max-width: 800px; margin: auto; padding-top: 140px; }
h2 { font-family: 'Poppins', sans-serif; font-weight: 600; margin-bottom: 25px; color: #333; }

/* Form Card */
.form-card {
    background: white;
    padding: 25px 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Services Cards */
.services-container { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px; }
.service-card {
    flex: 1 1 150px;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-align: center;
    cursor: pointer;
    transition: 0.3s;
}
.service-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
.service-card.selected { border: 2px solid #4BB543; background-color: #e0ffe0; }

/* Selected services */
.selected-services { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
.selected-service {
    background-color: #4BB543; color: white; padding: 8px 12px; border-radius: 8px;
    cursor: pointer;
}

/* Inputs */
.form-card label { font-weight: 600; color: #555; }
.form-card select, .form-card input[type="date"] {
    padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-size: 15px;
}

/* Button */
.form-card button {
    padding: 12px; border: none; border-radius: 8px;
    background-color: #4BB543; color: white; font-size: 16px;
    cursor: pointer; transition: background 0.3s;
}
.form-card button:hover { background-color: #3da236; }

/* Toast Notification */
.toast {
    visibility: hidden; min-width: 250px; margin-left: -125px;
    background-color: #333; color: #fff; text-align: center; border-radius: 8px;
    padding: 16px; position: fixed; z-index: 9999; left: 50%; bottom: 30px;
    font-size: 16px; opacity: 0; transition: opacity 0.5s, bottom 0.5s;
}
.toast.success { background-color: #4BB543; }
.toast.error { background-color: #FF3333; }
</style>
</head>
<body>

<?php include '../component/patient_navbar.php'; ?>

<!-- Toast -->
<div id="toast" class="toast <?php echo isset($success) ? 'success' : (isset($error) ? 'error' : ''); ?>">
    <?php if (isset($success)) echo $success; ?>
    <?php if (isset($error)) echo $error; ?>
</div>

<main class="container">
    <h2>Book Appointment</h2>

    <form class="form-card" action="book_appointment.php" method="POST">

        <!-- Selected services -->
        <div id="selected-services" class="selected-services"><h4>Selected Services:</h4></div>

        <!-- Services cards -->
        <div class="services-container">
            <?php
            $services = $connect->query("SELECT * FROM services");
            while ($row = $services->fetch_assoc()) {
                echo "<div class='service-card' data-id='{$row['id']}'>{$row['service_name']}</div>";
            }
            ?>
        </div>

        <!-- Hidden input -->
        <input type="hidden" name="service_ids[]" id="service_ids">

        <!-- Dentist -->
        <label>Dentist</label>
        <select name="dentist_id" required>
            <option value="">-- Select Dentist --</option>
            <?php
            $dentists = $connect->query("SELECT * FROM dentists WHERE status='Available'");
            while ($row = $dentists->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <!-- Date -->
        <label>Appointment Date</label>
        <input type="date" name="appointment_date" required>

        <!-- Time -->
        <label>Time Slot</label>
        <select name="appointment_time" required>
            <option value="">-- Select Time --</option>
            <option value="09:00">09:00 AM</option>
            <option value="10:00">10:00 AM</option>
            <option value="11:00">11:00 AM</option>
            <option value="13:00">01:00 PM</option>
            <option value="14:00">02:00 PM</option>
        </select>

        <button type="submit" name="book_appointment">Book Appointment</button>
    </form>
</main>

<script>
const selected = [];
const selectedServicesContainer = document.getElementById('selected-services');
const hiddenInput = document.getElementById('service_ids');

document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', () => {
        const id = card.dataset.id;
        if (selected.includes(id)) {
            selected.splice(selected.indexOf(id), 1);
            card.classList.remove('selected');
        } else {
            selected.push(id);
            card.classList.add('selected');
        }
        updateSelected();
    });
});

function updateSelected() {
    selectedServicesContainer.innerHTML = "<h4>Selected Services:</h4>";
    selected.forEach(id => {
        const name = document.querySelector(`.service-card[data-id='${id}']`).textContent;
        const span = document.createElement('div');
        span.textContent = name;
        span.classList.add('selected-service');
        span.onclick = () => {
            selected.splice(selected.indexOf(id), 1);
            document.querySelector(`.service-card[data-id='${id}']`).classList.remove('selected');
            updateSelected();
        };
        selectedServicesContainer.appendChild(span);
    });
    hiddenInput.value = selected; // Array of IDs
}

// Toast
window.onload = function() {
    const toast = document.getElementById('toast');
    if (toast && toast.textContent.trim() !== '') {
        toast.style.visibility = 'visible';
        toast.style.opacity = '1';
        toast.style.bottom = '50px';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.bottom = '30px';
            setTimeout(() => { toast.style.visibility = 'hidden'; }, 500);
        }, 3000);
    }
};
</script>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] === 'patient') {
    header("Location: ../login.php");
    exit();
}
include '../action/connection.php';

// --- DELETE PATIENT LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $sql = "DELETE FROM users WHERE id = $id AND user_type = 'patient'";
    if (mysqli_query($connect, $sql)) {
        $_SESSION['success_msg'] = "Patient record deleted successfully.";
    }
    header("Location: dentist_patient.php");
    exit();
}

// --- NEW PATIENT REGISTRATION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_patient'])) {
    $fname = mysqli_real_escape_string($connect, $_POST['f_name']);
    $lname = mysqli_real_escape_string($connect, $_POST['l_name']);
    $full_name = trim("$fname $lname");
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $contact = mysqli_real_escape_string($connect, $_POST['contact']);
    $gender = mysqli_real_escape_string($connect, $_POST['gender']);
    $birthday = mysqli_real_escape_string($connect, $_POST['birthday']);
    $history = mysqli_real_escape_string($connect, $_POST['dental_history']);
    
    $password = password_hash('Patient123!', PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, contact, password, user_type, gender, birthday, dental_history, created_at) 
            VALUES ('$full_name', '$email', '$contact', '$password', 'patient', '$gender', '$birthday', '$history', NOW())";

    if (mysqli_query($connect, $sql)) {
        $_SESSION['success_msg'] = "Patient added successfully!";
        header("Location: dentist_patient.php");
        exit();
    }
}

$query = "SELECT id, name, email, created_at FROM users WHERE user_type = 'patient' ORDER BY id DESC";
$result = $connect->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Records - Peter Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --peter-pink: #ff6b9d; --peter-blue: #0081C9; --sidebar-width: 260px; --bg-light: #fff5f8; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--bg-light); display: flex; }
        .main-container { margin-left: var(--sidebar-width); flex: 1; padding: 20px; width: calc(100% - var(--sidebar-width)); }
        .content-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-bar { background: #f8f8f8; border-radius: 20px; padding: 6px 15px; display: flex; align-items: center; width: 300px; border: 1px solid #eee; }
        .search-bar input { border: none; background: transparent; padding: 6px 10px; width: 100%; outline: none; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; color: #aaa; padding: 15px 10px; border-bottom: 2px solid #f8f8f8; }
        td { padding: 15px 10px; font-size: 14px; color: #555; border-bottom: 1px solid #fcfcfc; }
        
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center; }
        .modal-content { background: white; width: 90%; max-width: 800px; border-radius: 30px; padding: 40px; position: relative; max-height: 90vh; overflow-y: auto; }
        
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px; }
        .full-row { grid-column: span 2; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--peter-pink); margin-bottom: 5px; }
        .form-group input, .form-group textarea, .form-group select { background-color: #f0fff9; border: 1px solid #e0f2f1; padding: 12px 15px; border-radius: 12px; font-size: 14px; width: 100%; box-sizing: border-box; }
        
        .btn-new-patient { background: var(--peter-pink); color: white; padding: 10px 20px; border-radius: 25px; border: none; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-save-pill { background: var(--peter-pink); color: white; padding: 12px 60px; border-radius: 30px; border: none; font-weight: bold; cursor: pointer; }
        .btn-cancel-pill { background: white; color: var(--peter-pink); border: 2px solid var(--peter-pink); padding: 12px 60px; border-radius: 30px; font-weight: bold; cursor: pointer; margin-right: 10px; }
        .btn-action { background: #fff5f8; color: var(--peter-pink); padding: 8px 15px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <?php include '../component/sideBar_dentist.php'; ?>

    <div class="main-container">
        <?php include '../component/headerTop_dentist.php'; ?>

        <div class="content-card">
            <div class="table-header">
                <h2>Patient Management</h2>
                <div style="display:flex; gap:15px;">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="pSearch" placeholder="Search..." onkeyup="filterTable()">
                    </div>
                    <button class="btn-new-patient" onclick="openModal('newPatientModal')">
                        <i class="fa-solid fa-plus"></i> New Patient
                    </button>
                </div>
            </div>

            <table id="pTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                            <td style="display:flex; gap:5px;">
                                <button onclick="viewPatientHistory(<?php echo $row['id']; ?>)" class="btn-action">History</button>
                                <button onclick="deletePatient(<?php echo $row['id']; ?>)" class="btn-action" style="color:#ff4444;"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="newPatientModal" class="modal">
        <div class="modal-content">
            <h2 style="color: var(--peter-pink);">New Patient Entry</h2>
            <form action="" method="POST">
                <input type="hidden" name="register_patient" value="1">
                <div class="form-grid">
                    <div class="form-group"><label>First Name</label><input type="text" name="f_name" required></div>
                    <div class="form-group"><label>Last Name</label><input type="text" name="l_name" required></div>
                    <div class="form-group"><label>Birthday</label><input type="date" name="birthday" required></div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Contact</label><input type="text" name="contact" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Schedule Date</label><input type="date" name="schedule_date" required></div>
                    <div class="form-group">
                        <label>Schedule Time</label>
                        <select name="schedule_time" required>
                            <?php for($i=9; $i<=21; $i++) { $t=date("h:i A", strtotime("$i:00")); echo "<option value='$t'>$t</option>"; } ?>
                        </select>
                    </div>
                    <div class="form-group full-row"><label>Notes/History</label><textarea name="dental_history" rows="2"></textarea></div>
                </div>
                <div style="text-align: center; margin-top: 30px;">
                    <button type="button" class="btn-cancel-pill" onclick="closeModal('newPatientModal')">Cancel</button>
                    <button type="submit" class="btn-save-pill">Save Patient</button>
                </div>
            </form>
        </div>
    </div>

    <div id="historyModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div id="modalBody"></div>
        </div>
    </div>

<script>
    // 1. Initialize Toast Configuration (Top Right)
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // 2. Handle PHP Redirect Messages (New Patient / Delete)
    <?php if(isset($_SESSION['success_msg'])): ?>
        Toast.fire({
            icon: 'success',
            title: '<?php echo $_SESSION['success_msg']; ?>'
        });
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    function openModal(id) { document.getElementById(id).style.display = "flex"; }
    function closeModal(id) { document.getElementById(id).style.display = "none"; }

    // --- 3. THE UPDATE HANDLER (Confirmation THEN Toast) ---
    function confirmUpdate(event) {
        event.preventDefault(); 
        const form = event.target;
        
        // Show Confirmation Dialog First
        Swal.fire({
            title: 'Save Changes?',
            text: "Are you sure you want to update this medical history?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ff6b9d',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Yes, save it'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(form);
                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = "Saving...";
                btn.disabled = true;

                fetch('../action/update_patient_answers.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    
                    if(data.status === 'success') {
                        // Success Toast
                        Toast.fire({
                            icon: 'success',
                            title: 'History updated successfully'
                        });
                    } else {
                        Toast.fire({ icon: 'error', title: 'Update failed' });
                    }
                })
                .catch(error => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    Toast.fire({ icon: 'error', title: 'Connection error' });
                });
            }
        });
    }

    // --- 4. DELETE PATIENT (Confirmation THEN Redirect to Toast) ---
    function deletePatient(id) {
        closeModal('historyModal');
        Swal.fire({
            title: 'Delete Patient?',
            text: "This record and all history will be permanently removed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4444',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                // This redirects to PHP, which sets the session msg, then the Toast triggers on reload
                window.location.href = `dentist_patient.php?delete_id=${id}`;
            }
        });
    }

    // --- 5. SEARCH LOGIC ---
    function filterTable() {
        let input = document.getElementById("pSearch");
        let filter = input.value.toLowerCase();
        let table = document.getElementById("pTable");
        let tr = table.getElementsByTagName("tr");
        for (let i = 1; i < tr.length; i++) {
            let idCol = tr[i].getElementsByTagName("td")[0];
            let nameCol = tr[i].getElementsByTagName("td")[1];
            if (idCol && nameCol) {
                let idTxt = idCol.textContent || idCol.innerText;
                let nameTxt = nameCol.textContent || nameCol.innerText;
                tr[i].style.display = (idTxt.toLowerCase().indexOf(filter) > -1 || nameTxt.toLowerCase().indexOf(filter) > -1) ? "" : "none";
            }
        }
    }

    function viewPatientHistory(id) {
        openModal("historyModal");
        document.getElementById("modalBody").innerHTML = '<div style="text-align:center; padding:50px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        fetch(`../action/fetch_history.php?id=${id}`)
            .then(r => r.text())
            .then(data => { document.getElementById("modalBody").innerHTML = data; });
    }
</script>
</body>
</html>
<?php
session_start();
include '../action/connection.php';

// Security: Only Admin/Dentist
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] === 'patient') {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];

// 1. Fetch all existing questions
$query = "SELECT * FROM medical_questions ORDER BY id ASC";
$result = $connect->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Manager - Peter Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <style>
        :root {
            --peter-pink: #ff6b9d;
            --peter-blue: #0081C9; /* Your new color */
            --sidebar-width: 260px;
            --bg-light: #fff5f8;
        }

        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--bg-light); display: flex; }

        /* --- Sidebar --- */
        .sidebar { width: var(--sidebar-width); background-color: var(--peter-pink); height: 100vh; color: white; position: fixed; left: 0; top: 0; z-index: 1100; transition: var(--transition); padding-top: 30px; }
        .sidebar-header { text-align: center; margin-bottom: 30px; }
        .logo-circle { width: 80px; height: 80px; background: white; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 3px solid rgba(255,255,255,0.3); }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        
        .nav-menu { list-style: none; padding: 0; }
        .nav-item { padding: 15px 25px; display: flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 14px; transition: var(--transition); }
        .nav-item:hover, .nav-item.active { background: rgba(255, 255, 255, 0.2); border-left: 4px solid white; }

        /* Main Content */
        .main-container { margin-left: var(--sidebar-width); flex: 1; padding: 30px; width: calc(100% - var(--sidebar-width)); }
        
        .header-top { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }

        .content-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        
        /* Table Styling */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; color: #aaa; padding: 15px 10px; border-bottom: 2px solid #f8f8f8; text-transform: uppercase; }
        td { padding: 15px 10px; font-size: 14px; color: #555; border-bottom: 1px solid #fcfcfc; }

        /* Buttons */
        .btn-add { background: var(--peter-pink); color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; margin-bottom: 20px; }
        .btn-edit { color: #4dabf7; background: none; border: none; cursor: pointer; font-size: 16px; margin-right: 10px; }
        .btn-delete { color: #ff6b6b; background: none; border: none; cursor: pointer; font-size: 16px; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }
        .modal-content { background: #fff; margin: 10% auto; width: 400px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; margin-bottom: 5px; color: #666; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #eee; border-radius: 8px; box-sizing: border-box; }
        
        /* Toast Customization using your Blue color */
        .toast-success { background: var(--peter-blue) !important; box-shadow: 0 5px 15px rgba(0, 129, 201, 0.3); border-radius: 8px; }
        .toast-error { background: #ff6b6b !important; box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3); border-radius: 8px; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo-circle"><img src="../assets/brand/logo.JPG"></div>
            <h3 style="margin:0;">Peter Dental</h3>
        </div>
        <div class="nav-menu">
            <a href="dentist_dashboard.php" class="nav-item "><i class="fa-solid fa-house"></i> Home</a>
            <a href="dentist_patient.php" class="nav-item"><i class="fa-solid fa-user-group"></i> Patients</a>
            <a href="dentist_appointments.php" class="nav-item "><i class="fa-solid fa-calendar-days"></i> Appointments</a>
            <a href="dentist_Medical_Records.php" class="nav-item active"><i class="fa-solid fa-clipboard-list"></i> Medical Questions</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-gear"></i> Admin Settings</a>
        </div>
    </nav>

    <div class="main-container">
        <header class="header-top">
            <h2 style="margin:0; font-size:18px;">Manage Medical Questions</h2>
            <span style="color: var(--peter-pink); font-weight: 600;">Dr. <?php echo htmlspecialchars($admin_name); ?></span>
        </header>

        <button class="btn-add" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Add New Question
        </button>

        <div class="content-card">
            <table>
                <thead>
                    <tr>
                        <th width="10%">NO.</th>
                        <th width="70%">Question Text</th>
                        <th width="20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1; 
                    if ($result->num_rows > 0): 
                        while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: bold; color: var(--peter-pink);">#<?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['question_text']); ?></td>
                            <td>
                                <button class="btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['question_text']); ?>')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-delete" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center;">No questions found. Add one above!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="qModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle" style="color: var(--peter-pink); margin-top: 0;">Add Question</h3>
            <form action="../action/manage_questions.php" method="POST">
                <input type="hidden" id="q_id" name="q_id">
                <input type="hidden" id="action_type" name="action_type" value="add">
                
                <div class="form-group">
                    <label>Question Text</label>
                    <input type="text" id="question_text" name="question_text" placeholder="Enter medical question..." required>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-add" style="flex:1; margin-bottom:0;">Save Question</button>
                    <button type="button" onclick="closeModal()" style="flex:1; background:#eee; border:none; border-radius:10px; cursor:pointer; color:#777;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
    const modal = document.getElementById('qModal');
    const qForm = modal.querySelector('form');

    // Helper to show Toastify - UPDATED TO TOP RIGHT
    function showToast(msg, status) {
        let colorClass = (status === 'duplicate' || status === 'error') ? "toast-error" : "toast-success";
        Toastify({
            text: msg,
            duration: 3000,
            gravity: "top",      // top or bottom
            position: "right",   // Changed from left to right
            className: colorClass,
            stopOnFocus: true,
        }).showToast();
    }

    function openAddModal() {
        document.getElementById('modalTitle').innerText = "Add Question";
        document.getElementById('action_type').value = "add";
        document.getElementById('q_id').value = "";
        document.getElementById('question_text').value = "";
        modal.style.display = "block";
    }

    function openEditModal(id, text) {
        document.getElementById('modalTitle').innerText = "Edit Question";
        document.getElementById('action_type').value = "update";
        document.getElementById('q_id').value = id;
        document.getElementById('question_text').value = text;
        modal.style.display = "block";
    }

    function closeModal() { modal.style.display = "none"; }

    // AJAX FORM SUBMISSION
    qForm.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(qForm);

        fetch('../action/manage_questions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'updated') {
                closeModal();
                showToast(data.message, 'success');
                setTimeout(() => { location.reload(); }, 1000); 
            } else {
                showToast(data.message, data.status); 
            }
        })
        .catch(error => {
            showToast("An error occurred.", "error");
            console.error('Error:', error);
        });
    };

    // AJAX DELETE
    function confirmDelete(id) {
        if(confirm("Are you sure you want to delete this question?")) {
            fetch(`../action/manage_questions.php?delete_id=${id}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'deleted') {
                    showToast(data.message, 'success');
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }

    window.onclick = function(e) { if(e.target == modal) closeModal(); }
</script>
</body>
</html>
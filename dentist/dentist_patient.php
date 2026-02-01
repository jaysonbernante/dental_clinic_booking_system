<?php
session_start();
// Check if user is logged in as staff/dentist/admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] === 'patient') {
    header("Location: ../login.php");
    exit();
}
include '../action/connection.php';
$admin_name = $_SESSION['user_name'];

// Fetch all patients - Ordered by newest first
$query = "SELECT id, name, email, created_at FROM users ORDER BY id DESC";
$result = $connect->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records - Peter Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --peter-pink: #ff6b9d;
            --sidebar-width: 260px;
            --bg-light: #fff5f8;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--bg-light);
            display: flex;
        }

        /* --- Sidebar --- */
        .sidebar { width: var(--sidebar-width); background-color: var(--peter-pink); height: 100vh; color: white; position: fixed; left: 0; top: 0; z-index: 1100; transition: var(--transition); padding-top: 30px; }
        .sidebar-header { text-align: center; margin-bottom: 30px; }
        .logo-circle { width: 80px; height: 80px; background: white; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 3px solid rgba(255,255,255,0.3); }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        
        .nav-menu { list-style: none; padding: 0; }
        .nav-item { padding: 15px 25px; display: flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 14px; transition: var(--transition); }
        .nav-item:hover, .nav-item.active { background: rgba(255, 255, 255, 0.2); border-left: 4px solid white; }

        /* --- Main Content --- */
        .main-container {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 20px;
            transition: var(--transition);
            width: 100%;
        }

        /* --- Responsive Header --- */
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .header-left { display: flex; align-items: center; gap: 15px; flex: 1; }
        .burger-btn { display: none; background: none; border: none; font-size: 22px; color: var(--peter-pink); cursor: pointer; }
        
        .search-bar {
            background: #f8f8f8;
            border-radius: 20px;
            padding: 5px 15px;
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 350px;
        }
        .search-bar input {
            border: none;
            background: transparent;
            padding: 5px 10px;
            width: 100%;
            outline: none;
            font-size: 13px;
        }

        .header-right { display: flex; align-items: center; gap: 15px; }
        .admin-name { font-size: 13px; font-weight: 600; color: var(--peter-pink); }
        .profile-icon { width: 32px; height: 32px; background: var(--peter-pink); border-radius: 50%; }

        /* --- Table Styling --- */
        .content-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            overflow-x: auto; /* For mobile scroll */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th {
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: #aaa;
            padding: 15px 10px;
            border-bottom: 2px solid #f8f8f8;
        }

        td {
            padding: 15px 10px;
            font-size: 14px;
            color: #555;
            border-bottom: 1px solid #fcfcfc;
        }

        .patient-id { color: #aaa; font-weight: 600; }
        .patient-name { color: #333; font-weight: 600; }

        .btn-action {
            background: var(--bg-light);
            color: var(--peter-pink);
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-action:hover { background: var(--peter-pink); color: white; }

        /* --- Responsive Queries --- */
        @media (max-width: 992px) {
            .sidebar { left: -100%; }
            .sidebar.active { left: 0; }
            .main-container { margin-left: 0; }
            .burger-btn { display: block; }
        }
        /* Modal Background */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
}

/* Modal Box */
.modal-content {
    background-color: #fff;
    margin: 5% auto;
    width: 70%;
    max-width: 900px;
    border-radius: 20px;
    position: relative;
    animation: slideDown 0.3s ease-out;
    overflow: hidden;
}

@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.close-btn {
    position: absolute;
    right: 20px; top: 15px;
    font-size: 28px;
    color: #aaa;
    cursor: pointer;
    z-index: 10;
}

/* Modal Inner Layout (Matching your image) */
.modal-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    min-height: 500px;
}

.modal-sidebar {
    background: #fff;
    padding: 40px 20px;
    border-right: 1px solid #eee;
    text-align: center;
}

.modal-main {
    padding: 40px;
    background: #fff;
}

.profile-header-pink {
    background: var(--peter-pink);
    height: 100px;
    margin: -40px -20px 60px -20px;
}

.modal-profile-img {
    width: 100px; height: 100px;
    background: #f0f0f0;
    border: 5px solid white;
    border-radius: 50%;
    margin: 0 auto;
    margin-top: -50px;
}

.status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: var(--bg-light);
    border-radius: 10px;
    margin-top: 20px;
}

:root {
    --peter-blue: #0081C9; /* Adding your blue variable */
}

/* Custom Scrollbar for Modal */
.modal-main div::-webkit-scrollbar {
    width: 6px;
}
.modal-main div::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.modal-main div::-webkit-scrollbar-thumb {
    background: #ffb5cf;
    border-radius: 10px;
}
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
            <a href="dentist_patient.php" class="nav-item active"><i class="fa-solid fa-user-group"></i> Patients</a>
            <a href="dentist_appointments.php" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Appointments</a>
            <a href="dentist_Medical_Records.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Medical Questions</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-gear"></i> Admin Settings</a>
        </div>
    </nav>

    <div class="main-container">
        
        <header class="header-top">
            <div class="header-left">
                <button class="burger-btn" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass" style="color:#ccc;"></i>
                    <input type="text" id="pSearch" placeholder="Search by name or ID..." onkeyup="filterTable()">
                </div>
            </div>
            <div class="header-right">
                <span class="admin-name">Dr. <?php echo htmlspecialchars($admin_name); ?></span>
                <div class="profile-icon"></div>
            </div>
        </header>

        <div class="content-card">
            <h2 style="margin: 0 0 20px; font-size: 18px; color: #444;">Patient Management</h2>
            
            <table id="pTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Email Address</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="patient-id">#<?php echo $row['id']; ?></td>
                            <td class="patient-name"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                            <td>
                                <button onclick="viewPatientHistory(<?php echo $row['id']; ?>)" class="btn-action" style="border:none; cursor:pointer;">
    <i class="fa-solid fa-eye"></i> View History
</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No patients found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="historyModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div id="modalBody">
            <div style="text-align:center; padding: 50px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--peter-pink);"></i>
                <p>Loading History...</p>
            </div>
        </div>
    </div>
</div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        function filterTable() {
            let input = document.getElementById("pSearch");
            let filter = input.value.toUpperCase();
            let table = document.getElementById("pTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let nameCol = tr[i].getElementsByTagName("td")[1];
                let idCol = tr[i].getElementsByTagName("td")[0];
                if (nameCol || idCol) {
                    let nameTxt = nameCol.textContent || nameCol.innerText;
                    let idTxt = idCol.textContent || idCol.innerText;
                    if (nameTxt.toUpperCase().indexOf(filter) > -1 || idTxt.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }


        function viewPatientHistory(patientId) {
    const modal = document.getElementById("historyModal");
    const modalBody = document.getElementById("modalBody");
    modal.style.display = "block";
    
    fetch(`../action/fetch_history.php?id=${patientId}`)
        .then(response => response.text())
        .then(data => {
            modalBody.innerHTML = data;
            
            // Attach AJAX listener to the newly loaded form
            const form = document.getElementById('updateAnswersForm');
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('../action/update_patient_answers.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(resData => {
                    if(resData.status === 'success') {
                        // Use your showToast function from the previous page
                        Toastify({
                            text: resData.message,
                            position: "right",
                            className: "toast-success"
                        }).showToast();
                        closeModal();
                    }
                });
            };
        });
}

function closeModal() {
    document.getElementById("historyModal").style.display = "none";
}

// Close modal if clicking outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById("historyModal")) {
        closeModal();
    }
}
    </script>
</body>
</html>
<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// 2. Include Connection
include '../action/connection.php';

/**
 * 3. Connection Variable Fix
 */
if (!isset($conn)) {
    if (isset($connect)) { $conn = $connect; }
    elseif (isset($con)) { $conn = $con; }
    elseif (isset($db)) { $conn = $db; }
    else {
        die("Critical Error: Database connection variable not found.");
    }
}

$user_id = $_SESSION['user_id'];

// 4. Fetch User Data
$user_query = "SELECT * FROM users_managent WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_data = mysqli_fetch_assoc($user_result);
} else {
    $user_data = [
        'first_name' => 'User',
        'last_name' => 'Name',
        'email' => 'Not set',
        'profile_pix' => '',
        'birthday' => '',
        'mobile_number' => '',
        'gender' => 'Male',
        'address' => ''
    ];
}

// 5. Counters
$total_patients = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users_managent WHERE role = 'patient'"); 
if($res) { $row = mysqli_fetch_assoc($res); $total_patients = $row['total']; }

$new_patients = 3; 
$pending_requests = 1;

$admin_full_name = htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']);
$profile_img = !empty($user_data['profile_pix']) ? "../uploads/profile_pics/" . $user_data['profile_pix'] : "https://via.placeholder.com/150";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peter Dental - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --peter-pink: #ff6b9d;
            --peter-blue: #0081C9;
            --sidebar-width: 260px;
            --bg-light: #fff5f8;
            --transition: all 0.3s ease;
        }

        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--bg-light); display: flex; overflow-x: hidden; }

        /* --- Sidebar --- */
        .sidebar { width: var(--sidebar-width); background-color: var(--peter-pink); height: 100vh; color: white; position: fixed; left: 0; top: 0; z-index: 1100; transition: var(--transition); padding-top: 30px; }
        .sidebar-header { text-align: center; margin-bottom: 30px; }
        .logo-circle { width: 80px; height: 80px; background: white; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 3px solid rgba(255,255,255,0.3); }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        
        .nav-menu { list-style: none; padding: 0; }
        .nav-item { padding: 15px 25px; display: flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 14px; transition: var(--transition); }
        .nav-item:hover, .nav-item.active { background: rgba(255, 255, 255, 0.2); border-left: 4px solid white; }

        /* --- Main Content --- */
        .main-container { margin-left: var(--sidebar-width); flex: 1; padding: 0 20px 20px; width: calc(100% - var(--sidebar-width)); }

        /* --- Header & Profile --- */
        .header-top { display: flex; justify-content: space-between; align-items: center; background: white; padding: 12px 20px; border-radius: 12px; margin-top: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .profile-trigger { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .profile-img-nav { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; background: #eee; }
        
        .profile-dropdown { position: absolute; top: 45px; right: 0; background: white; width: 220px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); display: none; overflow: hidden; border: 1px solid #f0f0f0; z-index: 1001; }
        .dropdown-item { padding: 12px 20px; display: flex; align-items: center; gap: 10px; color: #444; text-decoration: none; font-size: 14px; cursor: pointer; }
        .dropdown-item:hover { background: #f8f9fa; color: var(--peter-blue); }

        /* --- Modals --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 2000; }
        .modal-content { background: white; border-radius: 15px; width: 90%; max-width: 500px; padding: 25px; }
        .modal-content.large { max-width: 800px; }
        .modal-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        
        .settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
        .form-group label { font-size: 12px; color: #666; font-weight: 600; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ddd; border-radius: 8px; }

        .btn-save { background: var(--peter-blue); color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; }
        .btn-cancel { background: transparent; border: 1px solid var(--peter-blue); color: var(--peter-blue); padding: 12px 30px; border-radius: 8px; cursor: pointer; }

        /* --- Dashboard Stats --- */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .section-title { font-size: 14px; font-weight: 700; color: #444; margin: 25px 0 15px; text-transform: uppercase; }
        .data-row { background: white; padding: 15px 20px; border-radius: 10px; display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }

        /* --- Updated Toast (Top-Right) --- */
        #toast {
            visibility: hidden;
            min-width: 280px;
            color: #fff;
            text-align: left;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 9999;
            right: 20px;
            top: -100px;
            font-size: 14px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: top 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.5s ease;
            opacity: 0;
        }
        #toast.show {
            visibility: visible;
            top: 20px;
            opacity: 1;
        }

        .fa-spin { margin-right: 8px; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo-circle"><img src="../assets/brand/logo.JPG"></div>
            <h3 style="margin:0;">Peter Dental</h3>
        </div>
        <div class="nav-menu">
            <a href="dentist_dashboard.php" class="nav-item active"><i class="fa-solid fa-house"></i> Home</a>
            <a href="dentist_patient.php" class="nav-item"><i class="fa-solid fa-user-group"></i> Patients</a>
            <a href="dentist_appointments.php" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Appointments</a>
            <a href="dentist_Medical_Records.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Medical Questions</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-gear"></i> Admin Settings</a>
        </div>
    </nav>

    <div class="main-container">
        <header class="header-top">
            <h4 style="margin:0; color:#444;">Dashboard</h4>
            <div class="header-right" style="display:flex; align-items:center; gap:20px;">
                <i class="fa-solid fa-bell" style="color:#aaa; cursor:pointer;"></i>
                <div class="profile-trigger" onclick="toggleDropdown(event)">
                    <span style="color:var(--peter-pink); font-weight:600; font-size:13px;">Dr. <?php echo htmlspecialchars($user_data['last_name']); ?></span>
                    <img src="<?php echo $profile_img; ?>" class="profile-img-nav">
                    
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-item" onclick="openModal('photoModal')"><i class="fa-solid fa-image"></i> Profile Photo</div>
                        <div class="dropdown-item" onclick="openModal('settingsModal')"><i class="fa-solid fa-user-gear"></i> Account Settings</div>
                        <a href="../action/logout.php" class="dropdown-item" style="color:red; border-top:1px solid #eee;"><i class="fa-solid fa-power-off"></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <div style="margin-top:25px;">
            <h2 style="margin:0;">Welcome Back, Doc <?php echo htmlspecialchars($user_data['first_name']); ?></h2>
            <p style="color:#888; font-size:13px;"><?php echo date("l, F j, Y"); ?></p>
        </div>

        <div class="section-title">Overview</div>
        <div class="stats-grid">
            <div class="stat-card"><h3><?php echo $total_patients; ?></h3><p>Total Patients</p></div>
            <div class="stat-card"><h3><?php echo $new_patients; ?></h3><p>New Patients</p></div>
            <div class="stat-card"><h3><?php echo $pending_requests; ?></h3><p>Pending Requests</p></div>
        </div>

        <div class="section-title">Appointments Today</div>
        <div class="data-row">
            <strong>06:30 PM</strong>
            <span>Patient 1</span>
            <span style="color:var(--peter-pink)">Checkup</span>
        </div>
    </div>

    <div class="modal-overlay" id="photoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Profile Photo</h3>
                <i class="fa-solid fa-xmark" onclick="closeModal('photoModal')" style="cursor:pointer"></i>
            </div>
            <form action="../action/update_profile.php" method="POST" enctype="multipart/form-data">
                <div style="text-align:center; padding:15px; background:#f9f9f9; border-radius:10px; margin-bottom:15px;">
                    <img id="preview" src="<?php echo $profile_img; ?>" style="width:180px; height:180px; object-fit:cover; border-radius:10px;">
                </div>
                <input type="file" name="profile_photo" id="fileInput" hidden onchange="previewImg(event)">
                <button type="button" onclick="document.getElementById('fileInput').click()" style="width:100%; padding:10px; background:var(--peter-blue); color:white; border:none; border-radius:5px; cursor:pointer; margin-bottom:10px;">Browse Image</button>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('photoModal')">Cancel</button>
                    <button type="submit" class="btn-save">Save Photo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="settingsModal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Edit Staff Account</h3>
                <i class="fa-solid fa-xmark" onclick="closeModal('settingsModal')" style="cursor:pointer"></i>
            </div>
            <form action="../action/update_profile.php" method="POST">
                <input type="hidden" name="update_settings" value="1">
                <div class="settings-grid">
                    <div class="form-group"><label>Email Address</label><input type="text" value="<?php echo $user_data['email']; ?>" readonly></div>
                    <div class="form-group">
                        <label>Password</label>
                        <button type="button" id="resetBtn" onclick="requestReset()" 
                                style="padding:10px; background:#e3f2fd; border:none; color:var(--peter-blue); border-radius:5px; cursor:pointer; width: 100%;">
                            <i class="fa-solid fa-paper-plane"></i> Send Reset Link to Gmail
                        </button>
                    </div>
                    <div class="form-group"><label>First Name *</label><input type="text" name="first_name" value="<?php echo $user_data['first_name']; ?>" required></div>
                    <div class="form-group"><label>Middle Name</label><input type="text" name="middle_name" value="<?php echo $user_data['middle_name']; ?>"></div>
                    <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" value="<?php echo $user_data['last_name']; ?>" required></div>
                    <div class="form-group"><label>Birthday</label><input type="date" name="birthday" value="<?php echo $user_data['birthday']; ?>"></div>
                    <div class="form-group"><label>Mobile Number</label><input type="text" name="mobile_number" value="<?php echo $user_data['mobile_number']; ?>"></div>
                    <div class="form-group"><label>Gender</label>
                        <select name="gender">
                            <option value="Male" <?php if($user_data['gender']=='Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if($user_data['gender']=='Female') echo 'selected'; ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>Address</label><input type="text" name="address" value="<?php echo $user_data['address']; ?>"></div>
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('settingsModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="confirmResetModal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <i class="fa-solid fa-circle-question" style="font-size: 50px; color: var(--peter-blue); margin-bottom: 15px;"></i>
            <h3>Are you sure?</h3>
            <p>A password reset link will be sent to:<br><strong><?php echo $user_data['email']; ?></strong></p>
            <div style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
                <button class="btn-cancel" onclick="closeModal('confirmResetModal')">Cancel</button>
                <button class="btn-save" onclick="executeReset()">Yes, Send it</button>
            </div>
        </div>
    </div>

    <div id="toast">
        <div id="toastIcon"></div>
        <span id="toastMsg"></span>
    </div>

    <script>
        function toggleDropdown(e) {
            e.stopPropagation();
            const dd = document.getElementById('profileDropdown');
            dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = () => { document.getElementById('profileDropdown').style.display = 'none'; }

        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function previewImg(event) {
            const reader = new FileReader();
            reader.onload = () => { document.getElementById('preview').src = reader.result; };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Trigger the Confirmation Modal
        function requestReset() {
            openModal('confirmResetModal');
        }

        // Execute the actual Send Link process
        function executeReset() {
            closeModal('confirmResetModal');
            
            const email = "<?php echo $user_data['email']; ?>";
            const btn = document.getElementById('resetBtn');
            
            btn.innerHTML = "<i class='fa-solid fa-circle-notch fa-spin'></i> Sending...";
            btn.disabled = true;

            fetch('../action/send_reset_link.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.text())
            .then(data => {
                if(data.trim() === "success") {
                    showToast("✅ Success! Check your Gmail inbox.", "success");
                } else {
                    showToast("❌ Error: " + data, "error");
                }
                btn.innerHTML = "<i class='fa-solid fa-paper-plane'></i> Send Reset Link to Gmail";
                btn.disabled = false;
            })
            .catch(error => {
                showToast("❌ Connection error.", "error");
                btn.disabled = false;
            });
        }

        // Top-Right Toast Logic
        function showToast(message, type) {
            const toast = document.getElementById("toast");
            const msg = document.getElementById("toastMsg");
            const icon = document.getElementById("toastIcon");

            // Set Peter Dental colors based on type
            if (type === "success") {
                toast.style.backgroundColor = "#0081C9"; // Peter Blue
                icon.innerHTML = '<i class="fa-solid fa-check-circle" style="font-size:18px;"></i>';
            } else {
                toast.style.backgroundColor = "#ff6b9d"; // Peter Pink
                icon.innerHTML = '<i class="fa-solid fa-exclamation-triangle" style="font-size:18px;"></i>';
            }

            msg.innerHTML = message;
            toast.classList.add("show");

            setTimeout(() => {
                toast.classList.remove("show");
            }, 4000);
        }
    </script>
</body>
</html>
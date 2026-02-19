<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';

if (!isset($conn)) {
    if (isset($connect)) { $conn = $connect; }
    elseif (isset($con)) { $conn = $con; }
    else { die("Database connection failed."); }
}

$user_id = $_SESSION['user_id'];

$user_query = "SELECT * FROM users_management WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user_data = mysqli_fetch_assoc($user_result);
} else {
    $user_data = ['first_name' => 'User', 'last_name' => 'Name', 'email' => ''];
}

// --- NOTIFICATION LOGIC (Only Unread is_read = 0) ---
// --- NOTIFICATION LOGIC (Gawing LIKE para hindi maselan sa spelling) ---
$res_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE status LIKE 'Pending' AND is_read = 0");
$pending_count = mysqli_fetch_assoc($res_pending)['total'] ?? 0;

$res_deferred = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE status LIKE 'Deferred' AND is_read = 0");
$deferred_count = mysqli_fetch_assoc($res_deferred)['total'] ?? 0;
$today = date('Y-m-d');
$res_patients = mysqli_query($conn, "SELECT COUNT(*) as total FROM users_management WHERE DATE(created_at) = '$today'");
$patient_count = mysqli_fetch_assoc($res_patients)['total'] ?? 0;

// Tip: Kung gusto mo appointments lang ang mag-trigger ng badge, tanggalin ang $patient_count dito
$total_notifications = $pending_count + $deferred_count + $patient_count;

$profile_img = !empty($user_data['profile_pix']) 
                ? "../uploads/profile_pics/" . $user_data['profile_pix'] 
                : "https://via.placeholder.com/150";
?>

<style>
    /* --- KEEPING YOUR EXACT CSS --- */
    :root {
        --peter-pink: #ff6b9d;
        --peter-blue: #0081C9;
        --sidebar-width: 260px;
        --bg-light: #fff5f8;
        --transition: all 0.3s ease;
    }
    .main-container { margin-left: var(--sidebar-width); flex: 1; padding: 0 20px 20px; width: calc(100% - var(--sidebar-width)); }
    .header-top { display: flex; justify-content: space-between; align-items: center; background: white; padding: 12px 20px; border-radius: 12px; margin-top: 20px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); }
    .profile-trigger { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
    .profile-img-nav { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; background: #eee; }
    .profile-dropdown { position: absolute; top: 45px; right: 0; background: white; width: 220px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); display: none; overflow: hidden; border: 1px solid #f0f0f0; z-index: 1001; }
    .dropdown-item { padding: 12px 20px; display: flex; align-items: center; gap: 10px; color: #444; text-decoration: none; font-size: 14px; cursor: pointer; }
    .dropdown-item:hover { background: #f8f9fa; color: var(--peter-blue); }
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: none; align-items: center; justify-content: center; z-index: 2000; }
    .modal-content { background: white; border-radius: 15px; width: 90%; max-width: 500px; padding: 25px; }
    .modal-content.large { max-width: 800px; }
    .modal-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
    .settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
    .form-group label { font-size: 12px; color: #666; font-weight: 600; }
    .form-group input, .form-group select { padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
    .btn-save { background: var(--peter-blue); color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; }
    .btn-cancel { background: transparent; border: 1px solid var(--peter-blue); color: var(--peter-blue); padding: 12px 30px; border-radius: 8px; cursor: pointer; }
    #toast { visibility: hidden; min-width: 280px; color: #fff; text-align: left; border-radius: 8px; padding: 16px; position: fixed; z-index: 9999; right: 20px; top: -100px; font-size: 14px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); display: flex; align-items: center; gap: 12px; transition: top 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.5s ease; opacity: 0; }
    #toast.show { visibility: visible; top: 20px; opacity: 1; }
    .fa-spin { margin-right: 8px; }

    /* --- NOTIFICATION CSS --- */
    .notification-wrapper { position: relative; cursor: pointer; display: flex; align-items: center; }
    .notification-badge { position: absolute; top: -8px; right: -8px; background: var(--peter-pink); color: white; font-size: 10px; padding: 2px 6px; border-radius: 50%; border: 2px solid white; font-weight: bold; }
    .notif-dropdown { position: absolute; top: 45px; right: 0; background: white; width: 280px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15); display: none; z-index: 1002; border: 1px solid #f0f0f0; overflow: hidden; }
    .notif-header { padding: 12px 15px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: 600; font-size: 13px; color: #444; }
    .notif-item { padding: 12px 15px; display: flex; align-items: center; gap: 12px; text-decoration: none; color: #555; font-size: 13px; border-bottom: 1px solid #f9f9f9; transition: background 0.2s; cursor: pointer; }
    .notif-item:hover { background: #fff5f8; }
    .notif-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; }
</style>

<header class="header-top">
    <h4 style="margin:0; color:#444;">
        <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?>
    </h4>

    <div class="header-right" style="display:flex; align-items:center; gap:20px;">
        
        <div class="notification-wrapper" onclick="toggleNotif(event)">
            <i class="fa-solid fa-bell" style="color:#aaa; cursor:pointer; font-size: 20px;"></i>
            <?php if ($total_notifications > 0): ?>
                <span class="notification-badge" id="mainBadge"><?php echo $total_notifications; ?></span>
            <?php endif; ?>

            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">Notifications</div>
                
                <?php if ($pending_count > 0): ?>
                <div class="notif-item" onclick="markAsRead('Pending', 'dentist_appointments.php?tab=requests&sub=pending')">
                    <div class="notif-icon" style="background: #fff3e0; color: #ff9800;"><i class="fa-solid fa-clock"></i></div>
                    <span><b><?php echo $pending_count; ?></b> Pending Appointments</span>
                </div>
                <?php endif; ?>

                <?php if ($deferred_count > 0): ?>
                <div class="notif-item" onclick="markAsRead('Deferred', 'dentist_appointments.php?tab=requests&sub=deferred')">
                    <div class="notif-icon" style="background: #fce4ec; color: #e91e63;"><i class="fa-solid fa-calendar-minus"></i></div>
                    <span><b><?php echo $deferred_count; ?></b> Deferred Requests</span>
                </div>
                <?php endif; ?>

                <?php if ($patient_count > 0): ?>
                <a href="patients_management.php" class="notif-item">
                    <div class="notif-icon" style="background: #e3f2fd; color: #2196f3;"><i class="fa-solid fa-user-plus"></i></div>
                    <span><b><?php echo $patient_count; ?></b> New Patients Today</span>
                </a>
                <?php endif; ?>

                <?php if ($total_notifications == 0): ?>
                    <div style="padding: 20px; text-align: center; color: #ccc; font-size: 12px;">No new notifications</div>
                <?php endif; ?>
            </div>
        </div>

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
                <h3>Account Setting</h3>
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
    // UPDATED MARK AS READ: Wait for DB update before redirecting
    function markAsRead(statusType, redirectUrl) {
    // Lipat agad ng page, sa PHP na natin gagawin ang update
    window.location.href = redirectUrl;
}

    function toggleNotif(e) {
        e.stopPropagation();
        const nd = document.getElementById('notifDropdown');
        const pd = document.getElementById('profileDropdown');
        pd.style.display = 'none';
        nd.style.display = (nd.style.display === 'block') ? 'none' : 'block';
    }

    function toggleDropdown(e) {
        e.stopPropagation();
        const dd = document.getElementById('profileDropdown');
        const nd = document.getElementById('notifDropdown');
        nd.style.display = 'none';
        dd.style.display = (dd.style.display === 'block') ? 'none' : 'block';
    }

    window.onclick = function(event) {
        if (!event.target.closest('.notification-wrapper')) {
            document.getElementById('notifDropdown').style.display = 'none';
        }
        if (!event.target.closest('.profile-trigger')) {
            document.getElementById('profileDropdown').style.display = 'none';
        }
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    
    function previewImg(event) {
        const reader = new FileReader();
        reader.onload = () => { document.getElementById('preview').src = reader.result; };
        reader.readAsDataURL(event.target.files[0]);
    }
    function requestReset() { openModal('confirmResetModal'); }
    
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
            if (data.trim() === "success") { showToast("✅ Success! Check your Gmail inbox.", "success"); }
            else { showToast("❌ Error: " + data, "error"); }
            btn.innerHTML = "<i class='fa-solid fa-paper-plane'></i> Send Reset Link to Gmail";
            btn.disabled = false;
        });
    }

    function showToast(message, type) {
        const toast = document.getElementById("toast");
        const msg = document.getElementById("toastMsg");
        const icon = document.getElementById("toastIcon");
        toast.style.backgroundColor = (type === "success") ? "#0081C9" : "#ff6b9d";
        icon.innerHTML = (type === "success") ? '<i class="fa-solid fa-check-circle"></i>' : '<i class="fa-solid fa-exclamation-triangle"></i>';
        msg.innerHTML = message;
        toast.classList.add("show");
        setTimeout(() => { toast.classList.remove("show"); }, 4000);
    }
</script>
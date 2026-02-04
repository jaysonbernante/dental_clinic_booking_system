<style>
  :root {
    --peter-pink: #ff6b9d;
    --peter-light-pink: #fff5f8;
    --text-dark: #333;
    --text-gray: #aaa;
  }

  /* Main Profile Card Container */
  .profile-card {
    background: white;
    border-radius: 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
  }

  .profile-bg { background: var(--peter-pink); height: 100px; }

  .profile-avatar {
    width: 100px; height: 100px; background: white; border-radius: 50%;
    margin: -50px auto 10px; border: 5px solid white;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  }

  .profile-avatar i { font-size: 50px; color: #eee; }
  .profile-body { text-align: center; padding: 10px 20px 25px; }
  .profile-body h2 { margin: 5px 0; color: var(--text-dark); font-size: 22px; }
  .profile-body .patient-id { color: var(--text-gray); font-size: 12px; margin-bottom: 15px; }

  .profile-actions { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; }

  .btn-mini {
    flex: 1; border: none; border-radius: 8px; padding: 10px;
    font-size: 11px; font-weight: 700; cursor: pointer;
    text-transform: uppercase; transition: transform 0.2s;
  }

  .btn-mini:active { transform: scale(0.95); }
  .btn-delete-light { background-color: #ffdbdb; color: #ff6b9d; }
  .btn-edit-light { background-color: #dbf0ff; color: #0081C9; }

  /* Patient Info Panel Container */
  .info-panel {
    background: #fff5f8;
    border-radius: 20px;
    padding: 20px;
    text-align: left;
    margin-top: 20px;
  }

  .info-panel h4 {
    margin: 0 0 15px;
    color: #888;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px 10px;
    text-align: left;
  }

  .info-item { display: flex; flex-direction: column; }
  .info-item label { font-size: 10px; color: #aaa; text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
  .info-item span { font-size: 13px; color: #333; font-weight: 600; line-height: 1.2; }

  .full-row { grid-column: span 2; }

  @media (max-width: 350px) {
    .info-grid { grid-template-columns: 1fr; }
    .full-row { grid-column: span 1; }
  }
</style>

<aside>
  <div class="profile-card">
    <div class="profile-bg"></div>
    <div class="profile-avatar"><i class="fa-solid fa-user"></i></div>

    <div class="profile-body">
      <h2><?php echo htmlspecialchars($user['name']); ?></h2>
      <p class="patient-id">ID: #<?php echo $user['id']; ?></p>

      <div class="profile-actions">
        <button type="button" class="btn-mini btn-delete-light" onclick="confirmDelete(<?php echo $user['id']; ?>)">Delete</button>
        <button type="button" class="btn-mini btn-edit-light" onclick="openEditModal()">Edit Profile</button>
      </div>

      <div class="info-panel">
        <h4>Patient Information</h4>
        <div class="info-grid">
          <div class="info-item">
            <label>Gender</label>
            <span><?php echo htmlspecialchars($user['gender'] ?? 'Not set'); ?></span>
          </div>
          <div class="info-item">
            <label>Birthday</label>
            <span><?php echo !empty($user['birthday']) ? date("M d, Y", strtotime($user['birthday'])) : 'Not set'; ?></span>
          </div>
          <div class="info-item">
            <label>Mobile Number</label>
            <span><?php echo htmlspecialchars($user['contact'] ?? 'Not set'); ?></span>
          </div>
          <div class="info-item">
            <label>Created Date</label>
            <span><?php echo date("M d, Y", strtotime($user['created_at'])); ?></span>
          </div>
          <div class="info-item full-row">
            <label>Email Address</label>
            <span><?php echo htmlspecialchars($user['email']); ?></span>
          </div>
          <div class="info-item full-row">
            <label>Address</label>
            <span><?php echo htmlspecialchars($user['address'] ?? 'No address provided'); ?></span>
          </div>
        </div>
        <p style="margin-top: 20px; text-align: center; opacity: 0.3; font-style: italic; font-size: 10px;">under maintenance</p>
      </div>
    </div>
  </div>
</aside>
<div id="editPatientModal" class="modal" style="display:none; position:fixed; z-index:999999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
  <div class="modal-content" style="background:#fff; width:90%; max-width:500px; margin:5% auto; border-radius:30px; padding:30px; position:relative;">
    <h2 style="color: var(--peter-pink); font-size: 20px; margin-bottom: 20px;">Edit My Profile</h2>
    <form id="patientEditForm" onsubmit="submitPatientUpdate(event)">
      <input type="hidden" name="user_id" id="modal_user_id" value="<?php echo $user['id']; ?>">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div style="grid-column: span 2;">
          <label style="font-size:11px; color:#aaa; font-weight:700;">FULL NAME</label>
          <input type="text" name="name" id="modal_name" style="width:100%; padding:12px; border-radius:12px; border:none; background:#f0fff9; margin-top:5px;" required>
        </div>
        <div style="grid-column: span 2;">
          <label style="font-size:11px; color:#aaa; font-weight:700;">EMAIL ADDRESS</label>
          <input type="email" name="email" id="modal_email" style="width:100%; padding:12px; border-radius:12px; border:none; background:#f0fff9; margin-top:5px;" required readonly>
        </div>
        <div>
          <label style="font-size:11px; color:#aaa; font-weight:700;">GENDER</label>
          <select name="gender" id="modal_gender" style="width:100%; padding:12px; border-radius:12px; border:none; background:#f0fff9; margin-top:5px;">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        <div>
          <label style="font-size:11px; color:#aaa; font-weight:700;">BIRTHDAY</label>
          <input type="date" name="birthday" id="modal_birthday" style="width:100%; padding:12px; border-radius:12px; border:none; background:#f0fff9; margin-top:5px;">
        </div>
        <div style="grid-column: span 2;">
          <label style="font-size:11px; color:#aaa; font-weight:700;">MOBILE NUMBER</label>
          <input type="text" name="contact" id="modal_contact" style="width:100%; padding:12px; border-radius:12px; border:none; background:#f0fff9; margin-top:5px;">
        </div>
        <div style="grid-column: span 2;">
          <label style="font-size:11px; color:#aaa; font-weight:700;">ADDRESS</label>
          <textarea name="address" id="modal_address" rows="2" style="width:100%; padding:12px; border-radius:12px; border:none; background:#f0fff9; margin-top:5px; font-family:inherit;"></textarea>
        </div>
      </div>
      <div style="margin-top: 25px; display: flex; gap: 10px;">
        <button type="button" onclick="closeEditModal()" style="flex:1; padding:12px; border-radius:12px; border:none; background:#eee; cursor:pointer; font-weight:600;">Cancel</button>
        <button onclick="closeEditModal()" type="submit" style="flex:1; padding:12px; border-radius:12px; border:none; background:var(--peter-pink); color:white; cursor:pointer; font-weight:600;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal() {
    const modal = document.getElementById('editPatientModal');
    
    // Move the modal to the very end of the <body> tag 
    // This allows z-index: 999999 to actually work against the header
    document.body.appendChild(modal);

    // Fill the fields as usual
    document.getElementById('modal_name').value = "<?php echo addslashes($user['name']); ?>";
    document.getElementById('modal_email').value = "<?php echo addslashes($user['email']); ?>";
    document.getElementById('modal_gender').value = "<?php echo $user['gender']; ?>";
    document.getElementById('modal_birthday').value = "<?php echo $user['birthday']; ?>";
    document.getElementById('modal_contact').value = "<?php echo addslashes($user['contact']); ?>";
    document.getElementById('modal_address').value = "<?php echo addslashes($user['address']); ?>";
    
    // Show it
    modal.style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editPatientModal').style.display = 'none';
}

function submitPatientUpdate(event) {
    event.preventDefault();
    const form = document.getElementById('patientEditForm');
    const formData = new FormData(form);

    // Validation
    if (formData.get('name').trim().length < 3) {
        showToast('Name is too short', 'error');
        return false;
    }

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    fetch('../action/update_patient_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            Toast.fire({ icon: 'success', title: 'Profile updated!' })
            .then(() => location.reload());
        } else {
            Toast.fire({ icon: 'error', title: data.message || 'Update failed' });
        }
    })
    .catch(error => {
        Toast.fire({ icon: 'error', title: 'Server connection error' });
    });

    return false;
}

function showToast(message, icon) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 3000
    });
}

function confirmDelete(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will permanently delete your account and all medical records. This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff6b9d', // Your Peter Pink color
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            // Perform the deletion via fetch
            fetch('../action/delete_patient_account.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + userId
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire(
                        'Deleted!',
                        'Your account has been removed.',
                        'success'
                    ).then(() => {
                        window.location.href = '../login.php'; // Redirect to login after deletion
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Could not delete account.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Server connection lost.', 'error');
            });
        }
    });
}
</script>
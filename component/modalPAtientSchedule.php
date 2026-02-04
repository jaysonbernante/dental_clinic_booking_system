<?php 
// Siguraduhin na may connection at $user data ka na galing sa database bago ito
// Halimbawa: $user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM users_management WHERE id = '$user_id'"));
?>

<style>
    :root {
        --peter-pink: #ff6b9d;
        --peter-light-pink: #fff5f8;
        --peter-mint: #ebfcf4;
        --text-dark: #444;
    }

    * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }

    /* Modal Styling */
    .modal {
        display: none; position: fixed; z-index: 2000; left: 0; top: 0;
        width: 100%; height: 100%; background: rgba(0,0,0,0.4);
        backdrop-filter: blur(4px); align-items: center; justify-content: center;
    }
    .modal-content {
        background: white; width: 95%; max-width: 800px; border-radius: 25px;
        padding: 35px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .modal-header h2 {
        color: var(--peter-pink); margin-top: 0; font-size: 22px;
        border-bottom: 2px solid var(--peter-pink); display: inline-block; padding-bottom: 5px;
    }

    .modal-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; }
    .full-width { grid-column: span 3; }
    .span-2 { grid-column: span 2; }

    .input-wrap { display: flex; flex-direction: column; }
    .input-wrap label { font-size: 12px; color: #999; margin-bottom: 4px; padding-left: 5px; text-transform: capitalize; }
    
    .input-wrap input, .input-wrap select, .input-wrap textarea {
        background: var(--peter-mint); border: none; border-radius: 12px;
        padding: 12px; font-size: 14px; width: 100%; outline: none;
    }

    /* Styling para sa hindi na pwedeng i-edit */
    input[readonly] { background: var(--peter-mint) !important; color: #888; cursor: not-allowed; border: 1px solid #eee; }
    
    .input-wrap textarea { height: 60px; resize: none; }

    .modal-footer { margin-top: 30px; display: flex; justify-content: center; gap: 20px; }
    .btn-modal { padding: 12px 50px; border-radius: 25px; font-weight: 600; cursor: pointer; border: none; font-size: 15px; transition: 0.3s; }
    .btn-cancel { background: white; color: var(--peter-pink); border: 2px solid var(--peter-pink); }
    .btn-save { background: var(--peter-pink); color: white; box-shadow: 0 4px 15px rgba(255,107,157,0.3); }

    /* SWAL FIX: Para laging nasa harap ng modal ang alert */
    .swal2-container { z-index: 3000 !important; }
</style>

<div id="appointmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h2>New Appointment</h2></div>

        <form id="scheduleForm">
            <div class="modal-grid">
                <div class="input-wrap full-width">
                    <label>Patient Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                </div>

                <div class="input-wrap">
                    <label>Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" <?php echo !empty($user['address']) ? 'readonly' : 'required'; ?>>
                </div>

                <div class="input-wrap">
                    <label>Birthday</label>
                    <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>" <?php echo (!empty($user['birthday']) && $user['birthday'] != '0000-00-00') ? 'readonly' : 'required'; ?>>
                </div>

                <div class="input-wrap">
                    <label>Gender</label>
                    <?php if (!empty($user['gender'])): ?>
                        <input type="text" value="<?php echo htmlspecialchars($user['gender']); ?>" readonly>
                        <input type="hidden" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>">
                    <?php else: ?>
                        <select name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="input-wrap">
                    <label>Contact Number</label>
                    <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>" required>
                </div>

                <div class="input-wrap">
                    <label>Date Schedule</label>
                    <input type="date" id="apt_date" name="schedule_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="input-wrap">
                    <label>Time</label>
                    <select id="apt_time" name="schedule_time" required>
                        <option value="">Select Date First</option>
                    </select>
                </div>

                <div class="input-wrap span-2">
                    <label>Assigned Dentist</label>
                    <select name="dentist_id" required>
                        <option value="">Select Dentist</option>
                        <?php
                        $dentists = mysqli_query($connect, "SELECT id, last_name FROM users_management WHERE role IN ('admin', 'dentist')");
                        while($d = mysqli_fetch_assoc($dentists)) {
                            echo "<option value='".$d['id']."'>Dr. ".$d['last_name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="input-wrap">
                    <label>Reason to Visit</label>
                    <select name="reason" required>
                        <option value="Check-up">Routine Check-up</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Extraction">Extraction</option>
                        <option value="Filling">Tooth Filling</option>
                    </select>
                </div>

                <div class="input-wrap full-width">
                    <label>Remark</label>
                    <textarea name="remark" placeholder="Any additional notes..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAptModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-save">Save Appointment</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openAptModal() { document.getElementById('appointmentModal').style.display = 'flex'; }
    function closeAptModal() { document.getElementById('appointmentModal').style.display = 'none'; }

    // Date Availability Checker
    document.getElementById('apt_date').addEventListener('change', function() {
        const date = this.value;
        const timeDrop = document.getElementById('apt_time');
        timeDrop.innerHTML = '<option>Checking...</option>';
        
        fetch(`../action/check_availability.php?date=${date}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'disabled') {
                    timeDrop.innerHTML = '<option value="">CLOSED</option>';
                    Swal.fire({
                        title: 'Clinic Closed',
                        text: 'Sorry, the clinic is closed on this date.',
                        icon: 'warning',
                        confirmButtonColor: '#ff6b9d'
                    });
                    this.value = '';
                } else {
                    const allSlots = ["08:00", "09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00"];
                    const bookedData = data.booked || [];
                    timeDrop.innerHTML = '<option value="">Select Time</option>';
                    allSlots.forEach(s => {
                        if (!bookedData.includes(s)) {
                            let hour = parseInt(s.split(':')[0]);
                            let display = hour >= 12 ? (hour==12?12:hour-12)+':00 PM' : hour+':00 AM';
                            timeDrop.innerHTML += `<option value="${s}">${display}</option>`;
                        }
                    });
                }
            });
    });

    // Form Submit
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('../action/save_appointment.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Saved!', showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    });

    // Outside click close
    window.onclick = function(event) {
        if (event.target == document.getElementById('appointmentModal')) closeAptModal();
    }
</script>
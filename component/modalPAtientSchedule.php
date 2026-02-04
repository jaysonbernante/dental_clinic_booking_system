<style>
    :root {
        --peter-pink: #ff6b9d;
        --peter-light-pink: #fff5f8;
        --peter-mint: #ebfcf4;
        --text-dark: #444;
    }

    * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }

    /* Appointment Modal Styling */
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
    .modal-header h2::before {
        content: ''; display: inline-block; width: 12px; height: 12px;
        background: var(--peter-pink); margin-right: 10px;
    }

    .modal-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px;
    }
    .full-width { grid-column: span 3; }
    .input-wrap { display: flex; flex-direction: column; }
    .input-wrap label { font-size: 12px; color: #999; margin-bottom: 4px; padding-left: 5px; text-transform: capitalize; }
    
    .input-wrap input, .input-wrap select, .input-wrap textarea {
        background: var(--peter-mint); border: none; border-radius: 12px;
        padding: 12px; font-size: 14px; width: 100%; outline: none;
    }
    .input-wrap input[readonly] { background: #f0f0f0; color: #888; cursor: not-allowed; }
    .input-wrap textarea { height: 60px; resize: none; }
    
    .span-2 { grid-column: span 2; }

    .modal-footer {
        margin-top: 30px; display: flex; justify-content: center; gap: 20px;
    }
    .btn-modal {
        padding: 12px 50px; border-radius: 25px; font-weight: 600; 
        cursor: pointer; border: none; font-size: 15px; transition: 0.3s;
    }
    .btn-cancel { background: white; color: var(--peter-pink); border: 2px solid var(--peter-pink); }
    .btn-cancel:hover { background: var(--peter-light-pink); }
    .btn-save { background: var(--peter-pink); color: white; box-shadow: 0 4px 15px rgba(255,107,157,0.3); }
    .btn-save:hover { background: #ff528a; transform: translateY(-2px); }

    /* Custom Pink Toast Styling */
    .swal2-timer-progress-bar { background: var(--peter-pink) !important; }
    .swal2-popup.swal2-toast { border-radius: 15px !important; border: 1px solid #ffd1e1 !important; }

    @media (max-width: 768px) {
        .modal-grid { grid-template-columns: 1fr; }
        .full-width, .span-2 { grid-column: span 1; }
        .modal-content { max-height: 90vh; overflow-y: auto; padding: 25px; }
        .modal-footer { flex-direction: column-reverse; }
        .btn-modal { width: 100%; }
    }
</style>

<div id="appointmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>New Appointment</h2>
        </div>

        <form id="scheduleForm">
            <div class="modal-grid">
                <div class="input-wrap full-width">
                    <label>Patient Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                </div>

                <div class="input-wrap"><label>address</label><input type="text" name="address" required></div>
                <div class="input-wrap"><label>Birthday</label><input type="date" name="birthday" required></div>
                <div class="input-wrap"><label>Gender</label>
                    <select name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="input-wrap"><label>contact number</label><input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>"></div>
                <div class="input-wrap"><label>Date Schedule</label><input type="date" id="apt_date" name="schedule_date" required min="<?php echo date('Y-m-d'); ?>"></div>
                <div class="input-wrap">
                    <label>Time</label>
                    <select id="apt_time" name="schedule_time" required>
                        <option value="">Select Time</option>
                        <option value="08:00">08:00 AM</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="13:00">01:00 PM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="16:00">04:00 PM</option>
                    </select>
                </div>

                <div class="input-wrap span-2"><label>Assigned Dentist</label>
                    <select name="dentist_id" required>
                        <option value="">Select Dentist</option>
                        <?php
                        $dentist_query = "SELECT id, last_name FROM users_management WHERE role = 'admin' OR role = 'dentist'";
                        $dentists = mysqli_query($connect, $dentist_query);
                        if(mysqli_num_rows($dentists) > 0) {
                            while($d = mysqli_fetch_assoc($dentists)) {
                                echo "<option value='".$d['id']."'>Dr. ".$d['last_name']."</option>";
                            }
                        } else {
                            echo "<option value=''>No Dentist Available</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="input-wrap"><label>Reason to Visit</label>
                    <select name="reason" required>
                        <option value="Check-up">Routine Check-up</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Extraction">Extraction</option>
                        <option value="Filling">Tooth Filling</option>
                    </select>
                </div>

                <div class="input-wrap full-width">
                    <label>Remark</label>
                    <textarea name="remark" placeholder="Write any additional notes here..."></textarea>
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
    if (typeof openAptModal !== 'function') {
        function openAptModal() { document.getElementById('appointmentModal').style.display = 'flex'; }
    }
    if (typeof closeAptModal !== 'function') {
        function closeAptModal() { document.getElementById('appointmentModal').style.display = 'none'; }
    }

    document.getElementById('apt_date').addEventListener('change', function() {
        const date = this.value;
        const timeDrop = document.getElementById('apt_time');
        timeDrop.innerHTML = '<option>Checking...</option>';
        fetch(`../action/check_availability.php?date=${date}`)
            .then(res => res.json())
            .then(bookedData => {
                const allSlots = ["08:00", "09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00"];
                timeDrop.innerHTML = '<option value="">Select Time</option>';
                allSlots.forEach(s => {
                    if (!bookedData.includes(s)) {
                        let opt = document.createElement('option');
                        opt.value = s; opt.textContent = s; timeDrop.appendChild(opt);
                    }
                });
            });
    });

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('appointmentModal');
        if (event.target === modal) closeAptModal();
    });

    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        e.preventDefault(); 
        closeAptModal();

        const formData = new FormData(this);

        fetch('../action/save_appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // --- TOASTED NOTIFICATION FOR SUCCESS ---
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Appointment Saved!',
                    text: 'Pending approval.',
                    iconColor: '#ff6b9d'
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'System error: ' + data.message,
                    icon: 'error',
                    confirmButtonColor: '#ff6b9d'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Could not connect to the server.', 'error');
        });
    });
</script>
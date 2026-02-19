<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
  header("Location: ../login.php");
  exit();
}

include '../action/connection.php';
$user_id = $_SESSION['user_id'];

// Fetch Profile & History (Keep your existing PHP logic)
$user_query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$user_result = mysqli_query($connect, $user_query);
$user = mysqli_fetch_assoc($user_result);

$history_query = "SELECT q.id as q_id, q.question_text, a.answer 
                  FROM medical_questions q
                  LEFT JOIN medical_answers a ON q.id = a.question_id AND a.user_id = '$user_id'
                  ORDER BY q.id ASC";
$history_result = mysqli_query($connect, $history_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Peter Dental</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* ... Keep your existing root and base styles ... */
    :root {
      --peter-pink: #ff6b9d;
      --peter-light-pink: #fff5f8;
      --peter-mint: #ebfcf4; /* The color from your image */
    }

    /* Appointment Modal Specific Styling */
    .modal-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-top: 20px;
    }

    .input-wrap.full { grid-column: span 2; }

    .input-wrap label {
      display: block;
      font-size: 13px;
      color: #888;
      margin-bottom: 5px;
    }

    .input-wrap input, .input-wrap select, .input-wrap textarea {
      width: 100%;
      padding: 12px;
      border-radius: 12px;
      border: none;
      background: var(--peter-mint); /* Mint green background */
      font-family: 'Poppins', sans-serif;
      box-sizing: border-box;
    }

    .readonly-input { background: #f0f0f0 !important; cursor: not-allowed; }

    .radio-choices { margin-top: 10px; display: flex; gap: 20px; font-size: 13px; color: #666; }

    .modal-footer {
      margin-top: 30px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn-save { background: var(--peter-pink); color: white; border: none; padding: 12px 35px; border-radius: 20px; font-weight: 600; cursor: pointer; }
    .btn-cancel { background: white; color: var(--peter-pink); border: 2px solid var(--peter-pink); padding: 10px 30px; border-radius: 20px; font-weight: 600; cursor: pointer; }
  </style>
</head>

<body>
  

  <div id="appointmentModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
      <h2 style="color: var(--peter-pink); border-bottom: 2px solid var(--peter-pink); display: inline-block; padding-bottom: 5px;">
        <span style="background: var(--peter-pink); width: 12px; height: 12px; display: inline-block; margin-right: 8px;"></span>
        New Appointment
      </h2>

      <form id="scheduleForm">
        <div class="modal-grid">
          <div class="input-wrap full">
            <label>Patient</label>
            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly class="readonly-input">
            <div class="radio-choices">
               <label><input type="radio" disabled> New Patient</label>
               <label><input type="radio" checked> Existing Patient</label>
            </div>
          </div>

          <div class="input-wrap"><label>Address</label><input type="text" name="address" required></div>
          <div class="input-wrap"><label>Birthday</label><input type="date" name="birthday" required></div>
          <div class="input-wrap"><label>Gender</label>
            <select name="gender"><option>Male</option><option>Female</option></select>
          </div>
          <div class="input-wrap"><label>Contact Number</label><input type="text" name="contact" required></div>
          
          <div class="input-wrap">
            <label>Date Schedule</label>
            <input type="date" id="apt_date" name="schedule_date" required min="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="input-wrap">
            <label>Time</label>
            <select id="apt_time" name="schedule_time" required><option value="">Select Date First</option></select>
          </div>

          <div class="input-wrap">
            <label>Assigned Dentist</label>
            <select name="dentist_id" required>
              <option value="">Select Dentist</option>
              <?php
                $dentist_query = "SELECT id, last_name FROM users_management WHERE role = 'dentist'";
                $dentists = mysqli_query($connect, $dentist_query);
                while($d = mysqli_fetch_assoc($dentists)) {
                  echo "<option value='".$d['id']."'>Dr. ".$d['last_name']."</option>";
                }
              ?>
            </select>
          </div>
          // Reason Dropdown with Common Dental Issues changed to a select dropdown for better UX
          <div class="input-wrap">
            <label>Reason to Visit</label>
            <select name="reason" required>
                <option value="Routine dental check-up">Routine dental check-up</option>
                <option value="Toothache / Dental pain">Toothache / Dental pain</option>
                <option value="Dental cleaning / Prophylaxis">Dental cleaning / Prophylaxis</option>
                <option value="Tooth filling">Tooth filling</option>
                <option value="Tooth extraction">Tooth extraction</option>
                <option value="Braces adjustment">Braces adjustment</option>
            </select>
          </div>

          <div class="input-wrap full"><label>Remark</label><textarea name="remark" rows="2"></textarea></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="closeAptModal()">Cancel</button>
          <button type="submit" class="btn-save">Save</button>
        </div>
      </form>
    </div>
  </div>

<script>
  // Existing Toast & History Modal Logic...
  
  function openAptModal() { document.getElementById('appointmentModal').style.display = 'flex'; }
  function closeAptModal() { document.getElementById('appointmentModal').style.display = 'none'; }

  // Date Change / Availability Logic
  document.getElementById('apt_date').addEventListener('change', function() {
    const selectedDate = this.value;
    const timeDropdown = document.getElementById('apt_time');
    timeDropdown.innerHTML = '<option>Checking...</option>';

    fetch(`../action/check_availability.php?date=${selectedDate}`)
      .then(res => res.json())
      .then(unavailable => {
          const allSlots = ["08:00", "09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00"];
          timeDropdown.innerHTML = '';
          const available = allSlots.filter(slot => !unavailable.includes(slot));

          if (available.length === 0) {
              timeDropdown.innerHTML = '<option value="">FULLY BOOKED</option>';
          } else {
              available.forEach(slot => {
                  let opt = document.createElement('option');
                  opt.value = slot; opt.textContent = slot;
                  timeDropdown.appendChild(opt);
              });
          }
      });
  });

  // Handle outside clicks for both modals
  window.onclick = function(event) {
    if (event.target.className === 'modal') {
      event.target.style.display = 'none';
    }
  }
</script>
</body>
</html>
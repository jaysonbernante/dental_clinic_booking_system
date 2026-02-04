<?php
session_start();
$pageTitle = "patient medicalHistory";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

include '../action/connection.php';
$user_id = $_SESSION['user_id'];

// 1. Fetch Patient Profile Data
$user_query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$user_result = mysqli_query($connect, $user_query);
$user = mysqli_fetch_assoc($user_result);

// 2. Fetch Medical Questions and the Patient's Answers
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
  <title>Medical History - Peter Dental</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --peter-pink: #ff6b9d;
      --peter-light-pink: #fff5f8;
      --peter-blue: #e3f2fd;
      --peter-blue-text: #2196f3;
    }

    /* Siguraduhin na ang body ay hindi nag-scroll */
body { 
    font-family: 'Poppins', sans-serif; 
    margin: 0; 
    background-color: #FDEDEE; 
    color: #444;
    height: 100vh; /* Full height ng screen */
    overflow: hidden; /* I-disable ang scroll sa main body */
}

/* Flexbox o Grid para sa layout */
.container {
    max-width: 1200px; 
    margin: 20px auto; 
    padding: 0 20px;
    display: grid; 
    grid-template-columns: 350px 1fr; 
    gap: 30px;
    height: calc(100vh - 100px); /* I-minus ang estimated height ng header */
    align-items: start;
}

/* Dito lang lalabas ang scrollbar */
main {
    height: 100%; /* Gamitin ang full height ng container */
    overflow-y: auto; /* Lalabas lang ang scroll kung hahaba ang content */
    padding-right: 15px; /* Space para hindi dikit ang scrollbar sa cards */
}

/* Custom Scrollbar para magmukhang malinis (Optional) */
main::-webkit-scrollbar {
    width: 6px;
}
main::-webkit-scrollbar-track {
    background: transparent;
}
main::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 10px;
}
main::-webkit-scrollbar-thumb:hover {
    background: var(--peter-pink);
}

/* Siguraduhin na ang profile card ay mananatili sa pwesto */
aside, .card-container {
    position: sticky;
    top: 0;
}

    /* History Card Styling */
    .history-card { 
        background: white;
        border-radius: 20px;
        padding: 40px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        
    }

    .history-header { 
       
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 30px;
        border-bottom: 2px solid var(--peter-light-pink);
        padding-bottom: 15px;
       
    }

    .history-header h2 { 
        color: var(--peter-pink); 
        margin: 0; 
        font-size: 26px;
        font-weight: 600;
    }

    .btn-update-history {
      background: var(--peter-blue); color: var(--peter-blue-text);
      padding: 10px 25px; border-radius: 12px; border: none; font-weight: 600; cursor: pointer;
      transition: 0.3s;
    }
    .btn-update-history:hover { background: #d1e9ff; }

    /* Question Rows */
    .q-row { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 15px 0; 
        border-bottom: 1px solid #f9f9f9; 
    }
    
    .ans-text { font-weight: 600; color: #777; }
    .ans-yes { color: var(--peter-pink); }

    /* Modal Styling */
    .modal {
      display: none; position: fixed; z-index: 2000; left: 0; top: 0;
      width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px); align-items: center; justify-content: center;
    }
    .modal-content {
      background: white; width: 90%; max-width: 600px; border-radius: 30px;
      padding: 40px; max-height: 85vh; overflow-y: auto;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .radio-group { display: flex; gap: 15px; }
    .radio-label { display: flex; align-items: center; gap: 5px; font-size: 14px; cursor: pointer; }

    @media (max-width: 900px) {
      .container { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>
  <?php 
  include '../component/headerTop_patient.php'; 
  ?>
  
  <div class="container">
    <aside>
        <?php include '../component/profileCard_patient.php'; ?>
    </aside>
    <?php include '../component/modalPatientSchedule.php'; ?>
  

    <main>
      <div class="history-card">
        <div class="history-header">
          <h2>Medical History</h2>
          <button class="btn-update-history" onclick="openUpdateModal()">
            <i class="fa-solid fa-pen-to-square"></i> Update
          </button>
        </div>
        
        <div class="history-body">
          <?php
          mysqli_data_seek($history_result, 0);
          while ($row = mysqli_fetch_assoc($history_result)): 
            $ans = $row['answer'] ? ucfirst(htmlspecialchars($row['answer'])) : 'No';
            $ans_class = (strtolower($ans) == 'yes') ? 'ans-yes' : '';
          ?>
            <div class="q-row">
              <span style="font-weight: 500; font-size: 14px;"><?php echo htmlspecialchars($row['question_text']); ?></span>
              <span class="ans-text <?php echo $ans_class; ?>"><?php echo $ans; ?></span>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
    </main>
  </div>

  <div id="updateHistoryModal" class="modal">
    <div class="modal-content">
      <h2 style="color: var(--peter-pink); margin-top: 0;">Update Medical History</h2>
      <p style="font-size: 12px; color: #888; margin-bottom: 20px;">Please answer all questions truthfully for your safety.</p>
      
      <form id="updateHistoryForm">
        <?php
        mysqli_data_seek($history_result, 0);
        while ($q = mysqli_fetch_assoc($history_result)):
          $is_yes = (strtolower($q['answer'] ?? '') === 'yes') ? 'checked' : '';
          $is_no  = (strtolower($q['answer'] ?? '') !== 'yes') ? 'checked' : '';
        ?>
          <div class="q-row">
            <span style="font-size: 13px; max-width: 70%;"><?php echo htmlspecialchars($q['question_text']); ?></span>
            <div class="radio-group">
              <label class="radio-label"><input type="radio" name="q_<?php echo $q['q_id']; ?>" value="yes" <?php echo $is_yes; ?>> Yes</label>
              <label class="radio-label"><input type="radio" name="q_<?php echo $q['q_id']; ?>" value="no" <?php echo $is_no; ?>> No</label>
            </div>
          </div>
        <?php endwhile; ?>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" onclick="closeModal()" style="padding: 12px 25px; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; background: #eee;">Cancel</button>
          <button type="submit" style="background: var(--peter-pink); color: white; padding: 12px 25px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['login_success'])): ?>
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

        Toast.fire({
            icon: 'success',
            title: 'Welcome back, <?php echo $_SESSION['user_name']; ?>!',
            text: 'Login successful.',
            iconColor: '#ff6b9d' // Pink theme mo
        });

        // I-unset ang session para hindi lumitaw ang toast uli pag nag-refresh
        <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>
});

    function openAptModal() {
  const modal = document.getElementById('appointmentModal');
  if (!modal) {
    console.error('Modal not found');
    return;
  }
  modal.style.display = 'flex';
}

function closeAptModal() {
  document.getElementById('appointmentModal').style.display = 'none';
}

/* Close when clicking background */
window.addEventListener('click', e => {
  const modal = document.getElementById('appointmentModal');
  if (e.target === modal) closeAptModal();
});
    function openUpdateModal() { 
        document.getElementById('updateHistoryModal').style.display = 'flex'; 
    }
    
    function closeModal() { 
        document.getElementById('updateHistoryModal').style.display = 'none'; 
    }

    const Toast = Swal.mixin({
      toast: true, position: 'top-end', showConfirmButton: false, timer: 2000
    });

    // History Update Logic
    document.getElementById('updateHistoryForm').addEventListener('submit', function(e) {
      e.preventDefault();
      closeModal();
      
      Swal.fire({
        title: 'Confirm Update?',
        text: "Your medical record will be updated.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ff6b9d',
        confirmButtonText: 'Yes, Save it'
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData(this);
          fetch('../action/update_patient_history.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Toast.fire({ icon: 'success', title: 'Medical History Updated' })
                     .then(() => location.reload());
              } else {
                Swal.fire('Error', 'Something went wrong.', 'error');
              }
            });
        } else { 
            openUpdateModal(); 
        }
      });
    });

    // Close on outside click
    window.onclick = function(event) {
      if (event.target.className === 'modal') {
        closeModal();
      }
    }
  </script>
</body>
</html>
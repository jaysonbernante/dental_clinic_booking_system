<?php
include 'connection.php';

if (!isset($_GET['id'])) {
    echo "No patient ID provided.";
    exit();
}

$patient_id = intval($_GET['id']);

$user_query = $connect->prepare("SELECT id, name, email, contact FROM users WHERE id = ?");
$user_query->bind_param("i", $patient_id);
$user_query->execute();
$patient = $user_query->get_result()->fetch_assoc();

$query = "SELECT q.id as q_id, q.question_text, COALESCE(a.answer, 'No') as current_answer 
          FROM medical_questions q 
          LEFT JOIN medical_answers a ON q.id = a.question_id AND a.user_id = ? 
          ORDER BY q.id ASC";

$stmt = $connect->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$records = $stmt->get_result();
?>

<button onclick="closeModal('historyModal')" 
        style="position: absolute; right: 25px; top: 25px; background: #f0f0f0; border: none; width: 35px; height: 35px; border-radius: 50%; color: #666; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; transition: 0.3s;"
        onmouseover="this.style.background='#ff6b9d'; this.style.color='white';" 
        onmouseout="this.style.background='#f0f0f0'; this.style.color='#666';">
    <i class="fa-solid fa-xmark" style="font-size: 18px;"></i>
</button>

<div class="modal-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 25px; position: relative;">
    <div class="modal-sidebar">
        <div class="profile-card-container" style="background: #fff5f8; border-radius: 20px; overflow: hidden; padding-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div class="profile-header-pink" style="background: #ff6b9d; height: 80px; width: 100%;"></div>
            <div class="modal-profile-img" style="width: 80px; height: 80px; background: white; border-radius: 50%; margin: -40px auto 10px; display: flex; align-items: center; justify-content: center; border: 4px solid white;">
                <i class="fa-solid fa-user" style="font-size: 35px; color: #ccc;"></i>
            </div>
            <h3 style="text-align: center; margin: 5px 0; font-size: 18px; color: #333;"><?php echo htmlspecialchars($patient['name']); ?></h3>
            <p style="text-align: center; font-size: 11px; color: #aaa; margin-bottom: 15px;">ID: #<?php echo $patient['id']; ?></p>
            
            <div style="display: flex; justify-content: center; padding: 0 15px;">
                <button type="button" onclick="deletePatient(<?php echo $patient['id']; ?>)" style="background: #ffdbdb; border: none; width: 100%; padding: 10px; border-radius: 8px; color: #ff6b9d; font-weight: 600; cursor: pointer;">
                    <i class="fa-solid fa-trash-can"></i> DELETE
                </button>
            </div>
        </div>

        <div class="patient-info-box" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 20px; text-align: left; border: 1px solid #f0f0f0;">
            <h4 style="margin: 0 0 10px; font-size: 14px; color: #555;">Patient Information</h4>
            <p style="font-size: 13px; color: #666; line-height: 1.6;">
                <strong>Contact:</strong> <?php echo $patient['contact'] ?? 'N/A'; ?><br>
                <strong>Email:</strong> <?php echo $patient['email']; ?><br>
                <span style="display: block; margin-top: 10px; font-style: italic; color: #aaa;">Data strictly confidential</span>
            </p>
        </div>
    </div>

    <div class="modal-main">
        <form id="updateAnswersForm" onsubmit="confirmUpdate(event)">
            <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-right: 40px;">
                <h2 style="margin:0; color: #ff6b9d; font-size: 20px;">Medical History</h2>
                <button onclick="closeModal('historyModal')"  type="submit" style="background: #0081C9; color: #fff; border: none; padding: 8px 25px; border-radius: 10px; font-weight: 600; cursor: pointer;">
                    Update
                </button>
            </div>

            <div style="max-height: 480px; overflow-y: auto; padding-right: 15px;">
                <?php while($row = $records->fetch_assoc()): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f5f5f5;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 8px; height: 8px; background: #ff6b9d; border-radius: 2px;"></div>
                            <span style="font-size: 13px; color: #444;"><?php echo htmlspecialchars($row['question_text']); ?></span>
                        </div>
                        
                        <div style="display: flex; gap: 15px;">
                            <label style="font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <input type="radio" name="answers[<?php echo $row['q_id']; ?>]" value="Yes" <?php echo ($row['current_answer'] == 'Yes') ? 'checked' : ''; ?>> Yes
                            </label>
                            <label style="font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <input type="radio" name="answers[<?php echo $row['q_id']; ?>]" value="No" <?php echo ($row['current_answer'] == 'No') ? 'checked' : ''; ?>> No
                            </label>
                        </div>
                    </div>  
                <?php endwhile; ?>
            </div>
        </form>
    </div>
</div>
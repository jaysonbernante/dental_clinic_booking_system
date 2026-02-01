<?php
include 'connection.php';

if (!isset($_GET['id'])) {
    echo "No patient ID provided.";
    exit();
}

$patient_id = intval($_GET['id']);

// Fetch Patient Info
$user_query = $connect->prepare("SELECT name, email FROM users WHERE id = ?");
$user_query->bind_param("i", $patient_id);
$user_query->execute();
$patient = $user_query->get_result()->fetch_assoc();

// Fetch Questions and Answers (Defaulting to 'No')
$query = "SELECT q.id as q_id, q.question_text, COALESCE(a.answer, 'No') as current_answer 
          FROM medical_questions q 
          LEFT JOIN medical_answers a ON q.id = a.question_id AND a.user_id = ? 
          ORDER BY q.id ASC";

$stmt = $connect->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$records = $stmt->get_result();
?>

<form id="updateAnswersForm">
    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
    <div class="modal-grid">
        <div class="modal-sidebar">
            <div class="profile-header-pink"></div>
            <div class="modal-profile-img" style="display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-user" style="font-size: 40px; color: #ccc;"></i>
            </div>
            <h3 style="margin: 15px 0 5px; font-size: 16px;"><?php echo htmlspecialchars($patient['name']); ?></h3>
            <p style="font-size: 12px; color: #777;">Patient Record</p>
        </div>

        <div class="modal-main">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid var(--bg-light); padding-bottom: 15px;">
                <h4 style="margin:0; color: var(--peter-pink); font-size: 16px;">Medical Questionnaire</h4>
                
                <button type="submit" style="
                    background-color: #0081C9; 
                    color: white; 
                    border: none; 
                    padding: 8px 25px; 
                    border-radius: 12px; 
                    font-weight: 600; 
                    cursor: pointer;
                    font-family: 'Poppins', sans-serif;
                    box-shadow: 0 4px 10px rgba(0, 129, 201, 0.2);
                    transition: transform 0.2s;
                " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    Update
                </button>
            </div>

            <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                <?php while($row = $records->fetch_assoc()): ?>
                    <div style="padding: 12px 0; border-bottom: 1px solid #f8f8f8; display: flex; justify-content: space-between; align-items: center; gap: 20px;">
                        <span style="font-size: 13px; color: #444; line-height: 1.4;">
                            <?php echo htmlspecialchars($row['question_text']); ?>
                        </span>
                        
                        <div style="display: flex; gap: 15px;">
                            <label style="cursor:pointer; font-size: 12px; display: flex; align-items: center; gap: 5px; font-weight: 500;">
                                <input type="radio" name="answers[<?php echo $row['q_id']; ?>]" value="Yes" <?php echo ($row['current_answer'] == 'Yes') ? 'checked' : ''; ?>> Yes
                            </label>
                            <label style="cursor:pointer; font-size: 12px; display: flex; align-items: center; gap: 5px; font-weight: 500;">
                                <input type="radio" name="answers[<?php echo $row['q_id']; ?>]" value="No" <?php echo ($row['current_answer'] == 'No') ? 'checked' : ''; ?>> No
                            </label>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</form>
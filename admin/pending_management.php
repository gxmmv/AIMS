<?php
// 1. Session and Error Reporting (Helps debug the "Blank Page")
if (session_status() === PHP_SESSION_NONE) { session_start(); }
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Load PHPMailer with the exact paths from your screenshot
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

require_once '../db_config.php';

// Security Check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// --- HANDLE APPROVAL / REJECTION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['student_id'];
    $new_status = $_POST['status']; 

    // Fetch student details to get Email and Student ID for credentials
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if ($student) {
        $update = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");
        if ($update->execute([$new_status, $id])) {
            
            // --- SEND EMAIL ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'gamelplaza5@gmail.com'; // YOUR GMAIL
                $mail->Password   = 'onjc mxwj gudf ijge';    // YOUR APP PASSWORD
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('gamelplaza5@gmail.com', 'SUNN AIMS Portal');
                $mail->addAddress($student['email']);

                $mail->isHTML(true);
                if ($new_status === 'approved') {
                    $mail->Subject = 'Account Approved - SUNN AIMS';
                    $mail->Body    = "<h3>Congratulations!</h3>
                                      <p>Your account is now active.</p>
                                      <p><b>Username:</b> {$student['student_id']}<br>
                                      <b>Password:</b> {$student['student_id']}</p>";
                } else {
                    $mail->Subject = 'Account Status - SUNN AIMS';
                    $mail->Body    = "<p>Your registration was declined. Please contact the registrar.</p>";
                }

                $mail->send();
                $alert = "Success! Status updated and email sent.";
            } catch (Exception $e) {
                $alert = "Status updated, but email failed: {$mail->ErrorInfo}";
            }
            echo "<script>alert('$alert'); window.location='admin_dashboard.php?page=pending';</script>";
        }
    }
}

// Fetch Pending Students
$stmt = $pdo->query("SELECT * FROM students WHERE status = 'pending' ORDER BY reg_date DESC");
$pending_students = $stmt->fetchAll();
?>

<link rel="stylesheet" href="../css/admin_pending.css?v=<?= time(); ?>">

<div class="mgmt-wrapper">
    <div class="mgmt-header">
        <div>
            <h2>Pending Registrations</h2>
            <p>Verify new student applications below.</p>
        </div>
        <div class="count-badge"><?= count($pending_students) ?> Pending</div>
    </div>

    <div class="table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th class="col-name">Student Name</th>
                    <th class="col-center">Course & Section</th>
                    <th class="col-center">Date Applied</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_students)): ?>
                    <tr><td colspan="4" class="empty-state">No pending records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($pending_students as $row): ?>
                    <tr>
                        <td class="col-name">
                            <div class="user-info">
                                <div class="user-init" style="background: #3498db;">
                                    <?= strtoupper(substr($row['first_name'], 0, 1)) ?>
                                </div>
                                <span class="user-full-name"><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name']) ?></span>
                            </div>
                        </td>
                        <td class="col-center">
                            <span class="course-tag"><?= htmlspecialchars($row['course'] . " " . $row['section']) ?></span>
                        </td>
                        <td class="col-center">
                            <span class="date-text"><?= date('M j, Y', strtotime($row['reg_date'])) ?></span>
                        </td>
                        <td class="col-action">
                            <button class="btn-review" onclick='openModal(<?= json_encode($row) ?>)'>Review</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="studentModal" class="glass-modal">
    <div class="registration-form-card">
        <div class="reg-header">
            <button class="reg-close" onclick="closeModal()">&times;</button>
            <div id="m-avatar" class="reg-avatar" style="background: #3498db;"></div>
            <div class="reg-header-text">
                <h3 id="m-name"></h3>
                <p>Registration Details</p>
            </div>
        </div>
        <div class="reg-body">
            <div class="reg-grid">
                <div class="reg-group"><label>ID</label><div class="reg-data-box" id="m-id"></div></div>
                <div class="reg-group"><label>Course</label><div class="reg-data-box" id="m-course"></div></div>
                <div class="reg-group"><label>Email</label><div class="reg-data-box" id="m-email"></div></div>
                <div class="reg-group"><label>Phone</label><div class="reg-data-box" id="m-phone"></div></div>
            </div>
        </div>
        <div class="reg-footer">
            <form method="POST">
                <input type="hidden" name="student_id" id="f-id">
                <input type="hidden" name="update_status" value="1">
                <div class="reg-btn-row">
                    <button type="submit" name="status" value="rejected" class="reg-btn reg-btn-red">Reject</button>
                    <button type="submit" name="status" value="approved" class="reg-btn reg-btn-blue" style="background: #3498db;">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(s) {
    document.getElementById('m-name').innerText = s.first_name + " " + s.last_name;
    document.getElementById('m-avatar').innerText = s.first_name[0].toUpperCase();
    document.getElementById('m-id').innerText = s.student_id;
    document.getElementById('m-course').innerText = s.course + " " + s.section;
    document.getElementById('m-email').innerText = s.email;
    document.getElementById('m-phone').innerText = s.phone;
    document.getElementById('f-id').value = s.id;
    document.getElementById('studentModal').classList.add('is-visible');
}
function closeModal() { document.getElementById('studentModal').classList.remove('is-visible'); }
</script>
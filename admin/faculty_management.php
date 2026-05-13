<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security: Redirect if not admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// --- HANDLE ADD FACULTY ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
    $user_display = trim($_POST['username']); 
    $email = trim($_POST['email']);
    $dept = trim($_POST['department']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // We assume the column is 'username'. If your DB uses 'name', change 'username' below.
    $insert = $pdo->prepare("INSERT INTO users (username, email, password, department, user_type) VALUES (?, ?, ?, ?, 'faculty')");
    
    if ($insert->execute([$user_display, $email, $pass, $dept])) {
        echo "<script>alert('Faculty added successfully!'); window.location='admin_dashboard.php?page=faculty';</script>";
    }
}

// --- HANDLE DELETE ---
if (isset($_GET['delete_id'])) {
    $delete = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'faculty'");
    $delete->execute([$_GET['delete_id']]);
    echo "<script>window.location='admin_dashboard.php?page=faculty';</script>";
}

// --- FETCH FACULTY MEMBERS (With Error Protection) ---
try {
    // Attempt to order by username
    $stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'faculty' ORDER BY username ASC");
} catch (PDOException $e) {
    // If 'username' column doesn't exist, try 'name'
    try {
        $stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'faculty' ORDER BY name ASC");
    } catch (PDOException $e) {
        // Ultimate fallback: Just get data without ordering to avoid the Fatal Error
        $stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'faculty'");
    }
}
$faculty_list = $stmt->fetchAll();
?>

<link rel="stylesheet" href="../css/admin_faculty.css?v=<?= time(); ?>">

<div class="mgmt-wrapper">
    <div class="mgmt-header">
        <div>
            <h2>Faculty Management</h2>
            <p>Manage instructor credentials and departmental assignments.</p>
        </div>
        <button class="btn-review" style="background: #2c3e50; color: #fff;" onclick="openAddModal()">+ Add New Faculty</button>
    </div>

    <div class="table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th class="col-name">Faculty Member</th>
                    <th class="col-center">Department</th>
                    <th class="col-center">Email Address</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($faculty_list)): ?>
                    <tr><td colspan="4" class="empty-state">No faculty members found.</td></tr>
                <?php else: ?>
                    <?php foreach ($faculty_list as $f): ?>
                    <tr>
                        <td class="col-name">
                            <div class="user-info">
                                <?php 
                                    // Use whatever name column exists
                                    $name = $f['username'] ?? $f['name'] ?? $f['fullname'] ?? 'User';
                                ?>
                                <div class="user-init" style="background: #2c3e50;"><?= strtoupper(substr($name, 0, 1)) ?></div>
                                <span class="user-full-name"><?= htmlspecialchars($name) ?></span>
                            </div>
                        </td>
                        <td class="col-center">
                            <span class="course-tag" style="background: #f1f5f9; color: #475569;">
                                <?= htmlspecialchars($f['department'] ?? 'Not Set') ?>
                            </span>
                        </td>
                        <td class="col-center">
                            <span class="date-text"><?= htmlspecialchars($f['email']) ?></span>
                        </td>
                        <td class="col-action">
                            <a href="?page=faculty&delete_id=<?= $f['id'] ?>" class="btn-review" style="color: #ef4444; border-color: #fee2e2;" onclick="return confirm('Permanently remove this instructor?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addFacultyModal" class="glass-modal">
    <div class="registration-form-card">
        <div class="reg-header">
            <button class="reg-close" onclick="closeAddModal()">&times;</button>
            <div class="reg-avatar" style="background: #2c3e50;">F</div>
            <div class="reg-header-text">
                <h3>Instructor Registration</h3>
                <p>Enter details for the new faculty account</p>
            </div>
        </div>

        <form method="POST">
            <div class="reg-body">
                <div class="reg-section-title">Profile Information</div>
                <div class="reg-grid">
                    <div class="reg-group">
                        <label>Full Name / Username</label>
                        <input type="text" name="username" class="reg-data-box" placeholder="e.g. John Doe" required style="width:100%; border:1.5px solid #e2e8f0;">
                    </div>
                    <div class="reg-group">
                        <label>Department</label>
                        <select name="department" class="reg-data-box" required style="width:100%; border:1.5px solid #e2e8f0;">
                            <option value="BSIT">BSIT</option>
                            <option value="BSHM">BSHM</option>
                            <option value="BSED">BSED</option>
                            <option value="CRIM">Criminology</option>
                        </select>
                    </div>
                </div>

                <div class="reg-section-title">Security & Access</div>
                <div class="reg-grid">
                    <div class="reg-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="reg-data-box" placeholder="faculty@aims.edu" required style="width:100%; border:1.5px solid #e2e8f0;">
                    </div>
                    <div class="reg-group">
                        <label>Temporary Password</label>
                        <input type="password" name="password" class="reg-data-box" required style="width:100%; border:1.5px solid #e2e8f0;">
                    </div>
                </div>
            </div>

            <div class="reg-footer">
                <div class="reg-btn-row">
                    <button type="button" class="reg-btn reg-btn-red" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" name="add_faculty" class="reg-btn reg-btn-blue" style="background: #2c3e50;">Register Faculty</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() { document.getElementById('addFacultyModal').classList.add('is-visible'); }
function closeAddModal() { document.getElementById('addFacultyModal').classList.remove('is-visible'); }

// Close modal if clicking outside the card
window.onclick = function(event) {
    let modal = document.getElementById('addFacultyModal');
    if (event.target == modal) {
        closeAddModal();
    }
}
</script>
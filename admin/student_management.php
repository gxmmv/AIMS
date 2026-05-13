<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../db_config.php';

// --- DATABASE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) {
        $sql = "INSERT INTO students (first_name, last_name, student_id, course, section, status) VALUES (?,?,?,?,?,'approved')";
        $pdo->prepare($sql)->execute([$_POST['fname'], $_POST['lname'], $_POST['sid'], $_POST['course'], $_POST['section']]);
        header("Location: admin_dashboard.php?page=student"); exit();
    }
    if (isset($_POST['update_student'])) {
        $sql = "UPDATE students SET first_name=?, last_name=?, student_id=?, course=?, section=? WHERE id=?";
        $pdo->prepare($sql)->execute([$_POST['fname'], $_POST['lname'], $_POST['sid'], $_POST['course'], $_POST['section'], $_POST['id']]);
        header("Location: admin_dashboard.php?page=student"); exit();
    }
}

if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$_GET['delete_id']]);
    header("Location: admin_dashboard.php?page=student"); exit();
}

$selected_course = $_GET['course'] ?? null;
$selected_section = $_GET['section'] ?? null;
$search_query = $_GET['search'] ?? null;
?>

<!-- The time() function forces the browser to load the freshest CSS version -->
<link rel="stylesheet" href="../css/management_style.css?v=<?= time(); ?>">

<div class="mgmt-container">
    <div class="mgmt-header-row">
        <div class="header-left-side">
            <h2 class="page-main-title">Student Management</h2>
            <div class="breadcrumbs">
                <a href="?page=student" class="breadcrumb-link">All</a>
                <?php if ($selected_course): ?>
                    <span class="sep">/</span> 
                    <span class="curr-page-text"><?= htmlspecialchars($selected_course) ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="header-controls">
            <form action="" method="GET" class="search-form-group">
                <input type="hidden" name="page" value="student">
                <input type="text" name="search" id="recordSearch" placeholder="Search by name or ID..." value="<?= htmlspecialchars($search_query ?? '') ?>">
            </form>
            <button class="btn-primary-add" onclick="openModal()">+ Add Student</button>
        </div>
    </div>

    <div class="content-card">
        <?php if ($search_query || ($selected_course && $selected_section)): 
            $sql = $search_query 
                ? "SELECT * FROM students WHERE first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ? ORDER BY last_name ASC" 
                : "SELECT * FROM students WHERE course = ? AND section = ? ORDER BY last_name ASC";
            $params = $search_query ? ["%$search_query%", "%$search_query%", "%$search_query%"] : [$selected_course, $selected_section];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
        ?>
            <div class="table-responsive">
                <table class="refined-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>ID Number</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $s): ?>
                        <tr>
                            <td data-label="Student Name"><strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong></td>
                            <td data-label="ID Number"><span class="id-badge"><?= htmlspecialchars($s['student_id']) ?></span></td>
                            <td data-label="Actions" class="action-cell">
                                <div class="action-btn-group">
                                    <button class="btn-sm btn-edit" onclick='openEditModal(<?= json_encode($s) ?>)'>Edit</button>
                                    <a href="?page=student&delete_id=<?= $s['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($results)): ?>
                            <tr><td colspan="3" style="text-align:center; padding:30px;">No students found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: 
            $sql = !$selected_course 
                ? "SELECT course as label, COUNT(*) as total FROM students GROUP BY course"
                : "SELECT section as label, COUNT(*) as total FROM students WHERE course = ? GROUP BY section";
            $stmt = $pdo->prepare($sql);
            $selected_course ? $stmt->execute([$selected_course]) : $stmt->execute();
            foreach ($stmt->fetchAll() as $row): 
                $link = !$selected_course ? "?page=student&course=".urlencode($row['label']) : "?page=student&course=".urlencode($selected_course)."&section=".urlencode($row['label']);
            ?>
                <a href="<?= $link ?>" class="stack-item">
                    <div class="item-info">
                        <span class="icon-box"><?= !$selected_course ? '📘' : '👥' ?></span>
                        <span class="title-text"><?= htmlspecialchars($row['label']) ?> (<?= $row['total'] ?>)</span>
                    </div>
                    <span class="chevron-arrow">→</span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Structure -->
<div id="studentModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Student Details</h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" id="studentForm">
            <input type="hidden" name="id" id="form_id">
            <div class="form-grid">
                <input type="text" name="fname" id="form_fname" placeholder="First Name" required>
                <input type="text" name="lname" id="form_lname" placeholder="Last Name" required>
                <input type="text" name="sid" id="form_sid" placeholder="Student ID" required>
                <input type="text" name="course" id="form_course" placeholder="Course (e.g. BSIT)" required>
                <input type="text" name="section" id="form_section" placeholder="Section (e.g. 2A)" required>
            </div>
            <button type="submit" name="add_student" id="submitBtn" class="btn-primary-add" style="width:100%; margin-top:15px;">Save Record</button>
        </form>
    </div>
</div>

<script>
function openModal() { document.getElementById('studentModal').style.display = 'flex'; }
function closeModal() { 
    document.getElementById('studentModal').style.display = 'none'; 
    document.getElementById('studentForm').reset();
    document.getElementById('submitBtn').name = 'add_student';
}
function openEditModal(d) {
    openModal();
    document.getElementById('submitBtn').name = 'update_student';
    document.getElementById('form_id').value = d.id;
    document.getElementById('form_fname').value = d.first_name;
    document.getElementById('form_lname').value = d.last_name;
    document.getElementById('form_sid').value = d.student_id;
    document.getElementById('form_course').value = d.course;
    document.getElementById('form_section').value = d.section;
}
window.onclick = function(event) {
    if (event.target == document.getElementById('studentModal')) closeModal();
}
</script>
<?php
/**
 * admin_dashboard.php - Complete Integrated Dashboard
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. DATABASE CONNECTION
require_once '../db_config.php';

// 2. SECURITY CHECK
if (!isset($_SESSION['loggedin']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// 3. ADMIN INFO
$admin_name = $_SESSION['username'] ?? 'Admin';
$initial = strtoupper(substr($admin_name, 0, 1)); 
$current_page = $_GET['page'] ?? 'profile';

// 4. DASHBOARD COUNTERS
try {
    $count_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'approved'")->fetchColumn() ?: 0;
    $faculty = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'faculty'")->fetchColumn() ?: 0;
    $count_admins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn() ?: 0;

    $total_users = $count_students + $faculty + $count_admins;
    $pending = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'pending'")->fetchColumn() ?: 0;

} catch (PDOException $e) {
    $total_users = 0; 
    $pending = 0; 
    $faculty = 0;
}

// 5. PAGE TITLES
$page_titles = [
    'profile' => 'My Profile',
    'faculty' => 'Faculty Management',
    'student' => 'Student Management',
    'pending' => 'Pending Students'
];
$display_title = $page_titles[$current_page] ?? 'My Profile';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUNN AIMS Portal - Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/Sunn_logo.png" alt="SUNN Logo">
        <h2>SUNN</h2>
        <p>AIMS Portal Admin</p>
    </div>
    
    <hr class="sidebar-divider">
    
    <nav class="sidebar-nav">
        <ul>
            <li><a href="?page=profile" class="<?= $current_page == 'profile' ? 'active' : '' ?>">My Profile</a></li>
            <li><a href="?page=faculty" class="<?= $current_page == 'faculty' ? 'active' : '' ?>">Faculty Management</a></li>
            <li><a href="?page=student" class="<?= $current_page == 'student' ? 'active' : '' ?>">Student Management</a></li>
            <li><a href="?page=pending" class="<?= $current_page == 'pending' ? 'active' : '' ?>">Pending Students</a></li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn-solid" onclick="return confirm('Confirm Logout?')">
            Log Out
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <header class="main-header">
        <!-- HAMBURGER BUTTON -->
        <button class="mobile-toggle" id="mobileToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <h1><?= $display_title ?></h1>
    </header>

    <!-- DIM OVERLAY FOR MOBILE SIDEBAR -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="content-area">
        <?php if ($current_page == 'profile'): ?>
            <div class="profile-card">
                <div class="profile-header-info">
                    <div class="profile-avatar"><?= $initial ?></div>
                    <div class="profile-text-block">
                        <h2 class="admin-name"><?= htmlspecialchars($admin_name) ?></h2>
                        <div class="role-badge-container">
                            <span class="role-badge">System Administrator</span>
                        </div>
                    </div>
                </div>

                <hr class="profile-separator">

                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-label">TOTAL USERS</span>
                        <span class="stat-number"><?= $total_users ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">PENDING REGISTRATIONS</span>
                        <span class="stat-number"><?= $pending ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">FACULTY MEMBERS</span>
                        <span class="stat-number"><?= $faculty ?></span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php 
                $file = $current_page . '_management.php';
                if (file_exists($file)) {
                    include_once $file;
                } else {
                    echo "<p>Management file not found.</p>";
                }
            ?>
        <?php endif; ?>
    </div>
</div>

<!-- INTERACTIVE SCRIPT -->
<script>
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        mobileToggle.classList.toggle('active');
    }

    mobileToggle.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);
</script>

</body>
</html>
<?php
session_start();
require_once '../db_config.php'; 

/**
 * FIXED: No more "Unauthorized Access" error.
 * If logged in, go to dashboard. If not, just show this form.
 */
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Set session variables to match your dashboard checks
            $_SESSION['loggedin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_type'] = 'admin';

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SUNN AIMS</title>
    <link rel="stylesheet" href="../css/admin_login.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">

    <div class="login-card">
        <div class="brand-panel">
            <img src="../img/Sunn_logo.png" alt="SUNN Logo">
            <h2>SUNN</h2>
            <p>AIMS Portal Administrator</p>
        </div>

        <div class="form-panel">
            <h3>Admin Login</h3>

            <?php if ($error): ?>
                <div class="error-box">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn-login">Login to Dashboard</button>
            </form>

            <div class="footer-links">
                <a href="../index.php">← Return to Website</a>
            </div>
        </div>
    </div>

</body>
</html>
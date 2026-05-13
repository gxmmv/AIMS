<?php
// 1. Path to your DB config (one folder up)
require_once '../db_config.php';

/**
 * 2. ACCOUNT CREDENTIALS
 * Username: Plaza
 * Password: Basta@123
 */
$user = 'Plaza'; 
$pass = 'Basta@123'; 
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

try {
    // Check if the username already exists to prevent "Duplicate Entry" errors
    $check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
    $check->execute([$user]);
    
    if ($check->fetchColumn() > 0) {
        echo "<strong>Notice:</strong> An admin account with the username <em>'$user'</em> already exists.";
    } else {
        // Insert the new admin
        $sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user, $hashed_pass]);
        
        echo "<div style='color: green; font-family: sans-serif;'>";
        echo "<h2>Success!</h2>";
        echo "<p>Admin account created successfully for <strong>$user</strong>.</p>";
        echo "<p><a href='admin_login.php'>Click here to Login</a></p>";
        echo "</div>";
    }
} catch (PDOException $e) {
    // This will catch issues like the 'admins' table missing
    echo "<div style='color: red; font-family: sans-serif;'>";
    echo "<h2>Error Creating Account</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
<?php
// Define the university information.
$university_name = "State University of Northern Negros";
$location = "Sagay City, Negros Occidental";

// Define the buttons with their specific login/application links.
$buttons = [
    "Administrator" => "admin/admin_login.php", 
    "Faculty"       => "faculty_login.php", 
    "Student"       => "student_login.php", 
    "Registration"  => "registration.php", 
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Portal Login</title>
    <!-- Link to your external CSS file -->
    <link rel="stylesheet" href="css/index.css">
</head>

<!-- We add the 'portal-body' class here so it targets only this page in style.css -->
<body class="portal-body">

    <div class="container">
        <div class="logo-placeholder">
            <img src="img/sunn_logo.png" alt="University Logo">
        </div>
        
        <div class="text-header">
            <h2><?php echo $university_name; ?></h2>
            <p><?php echo $location; ?></p>
        </div>

        <div class="button-group">
            <?php 
            foreach ($buttons as $label => $link) {
                echo '<a href="' . $link . '">' . $label . '</a>';
            }
            ?>
        </div>
    </div>

</body>
</html>
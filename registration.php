<?php
/**
 * SUNN AIMS Portal - Registration System v1.3
 * Features: Multi-layer validation, Auto-clear, and Phone Pattern matching.
 */
require_once 'db_config.php';

$message = "";
$error = "";
$registration_success = false;

// Dropdown Options
$course_list = ["BSIT", "BSHM", "BSBA", "BEED", "BSED", "BSCRIM"];
$section_list = ["1-A", "1-B", "2-A", "2-B", "3-A", "3-B", "4-A", "4-B"];

// Variables to keep form "Sticky"
$first_name = $last_name = $student_id = $course = $section = $phone_number = $email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $student_id   = trim($_POST['student_id'] ?? '');
    $course       = $_POST['course'] ?? '';
    $section      = $_POST['section'] ?? '';
    $country_code = $_POST['country_code'] ?? '';
    $phone_number = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');

    // 1. Check for Empty Fields
    if (empty($first_name) || empty($last_name) || empty($student_id) || empty($course) || empty($section) || empty($phone_number) || empty($email)) {
        $error = "All fields are required.";
    } 
    // 2. Name Validation (Min 2 letters, no numbers)
    elseif (!preg_match("/^[a-zA-Z\s]{2,}$/", $first_name)) {
        $error = "First name must be at least 2 letters (no numbers).";
    }
    elseif (!preg_match("/^[a-zA-Z\s]{2,}$/", $last_name)) {
        $error = "Last name must be at least 2 letters (no numbers).";
    }
    // 3. Student ID Validation (Format: 24-1234)
    elseif (!preg_match("/^\d{2}-\d{4,6}$/", $student_id)) {
        $error = "Invalid Student ID format. Use YY-XXXX (e.g., 24-1234).";
    }
    // 4. Email Format Check
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    // 5. Phone Validation Logic
    else {
        $isValidPhone = false;
        if ($country_code == "+63") {
            if (preg_match('/^9\d{9}$/', $phone_number)) { $isValidPhone = true; }
            else { $error = "PH Numbers must start with 9 and have 10 digits total."; }
        } elseif ($country_code == "+1") {
            if (preg_match('/^\d{10}$/', $phone_number)) { $isValidPhone = true; }
            else { $error = "US Numbers must be exactly 10 digits."; }
        }

        // DATABASE INSERTION
        if ($isValidPhone) {
            $full_phone = $country_code . " " . $phone_number;
            try {
                $sql = "INSERT INTO students (first_name, last_name, student_id, course, section, phone, email) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$first_name, $last_name, $student_id, $course, $section, $full_phone, $email])) {
                    $message = "Student successfully registered!";
                    $registration_success = true;
                    // Clear PHP variables to empty the form values
                    $first_name = $last_name = $student_id = $phone_number = $email = $course = $section = "";
                }
            } catch (PDOException $e) {
                // Handle Duplicate Student ID Error
                $error = ($e->getCode() == 23000) ? "Student ID already exists." : "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUNN - Registration</title>
    <!-- CSS Link with time-based versioning to prevent caching -->
    <link rel="stylesheet" href="css/registration.css?v=<?php echo time(); ?>">
</head>
<body class="registration-page">

    <div class="registration-card">
        
        <!-- Left Panel: Branding -->
        <div class="logo-panel">
            <div class="branding-content">
                <img src="img/Sunn_logo.png" alt="SUNN Logo" class="university-logo">
                <h3>SUNN</h3>
                <p>State University of Northern Negros</p>
                <div class="divider"></div>
                <span>AIMS Portal v1.3</span>
            </div>
        </div>

        <!-- Right Panel: Form Entry -->
        <div class="form-panel">
            <div class="form-header">
                <h2>Account Registration</h2>
                <p>Register your student profile below.</p>
            </div>

            <?php if ($error): ?><div class="error-msg"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($message): ?><div class="success-msg"><?php echo $message; ?></div><?php endif; ?>

            <form method="POST" action="" id="regForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required 
                               pattern="[A-Za-z\s]{2,}" title="Minimum 2 letters, no numbers"
                               value="<?php echo htmlspecialchars($first_name); ?>">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required 
                               pattern="[A-Za-z\s]{2,}" title="Minimum 2 letters, no numbers"
                               value="<?php echo htmlspecialchars($last_name); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="student@email.com" 
                           value="<?php echo htmlspecialchars($email); ?>">
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <div class="phone-wrapper">
                        <select name="country_code" id="countryCode">
                            <option value="+63">PH (+63)</option>
                            <option value="+1">US (+1)</option>
                        </select>
                        <input type="tel" name="phone" id="phoneInput" required 
                               pattern="9[0-9]{9}" placeholder="9123456789" 
                               value="<?php echo htmlspecialchars($phone_number); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" required 
                           pattern="\d{2}-\d{4,6}" placeholder="24-1234"
                           value="<?php echo htmlspecialchars($student_id); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Course</label>
                        <select name="course" required>
                            <option value="">Select</option>
                            <?php foreach($course_list as $c): ?>
                                <option value="<?= $c ?>" <?= ($course == $c) ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section</label>
                        <select name="section" required>
                            <option value="">Select</option>
                            <?php foreach($section_list as $s): ?>
                                <option value="<?= $s ?>" <?= ($section == $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Register Student</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">← Back to Portal</a>
            </div>
        </div>
    </div>

    <script>
        // 1. Final Form Clear on Success
        <?php if ($registration_success): ?>
            document.getElementById('regForm').reset();
        <?php endif; ?>

        // 2. Dynamic Pattern Update
        const countrySelect = document.getElementById('countryCode');
        const phoneInput = document.getElementById('phoneInput');

        countrySelect.addEventListener('change', function() {
            if (this.value === '+63') {
                phoneInput.setAttribute('pattern', '9[0-9]{9}');
                phoneInput.placeholder = "9123456789";
            } else {
                phoneInput.setAttribute('pattern', '[0-9]{10}');
                phoneInput.placeholder = "1234567890";
            }
        });
    </script>
</body>
</html>
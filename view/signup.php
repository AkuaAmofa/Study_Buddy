<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = get_db_connection();
        
        // Insert new user data
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, major, interests, profile_picture)
            VALUES (:username, :email, :password, :first_name, :last_name, :major, :interests, :profile_picture)
        ");
        $stmt->execute([
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'major' => $_POST['major'],
            'interests' => $_POST['interests'],
            'profile_picture' => $_FILES['profile_picture']['name']
        ]);

        // Handle file upload
        if (isset($_FILES['profile_picture'])) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        }

        // Redirect to home.php after successful signup
        header('Location: home.php');
        exit();
        
    } catch (Exception $e) {
        $error_message = "An error occurred during signup: " . $e->getMessage();
        log_message("Error in signup page: " . $e->getMessage(), 'ERROR');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="major">Major:</label>
                <input type="text" name="major" id="major">
            </div>
            <div class="form-group">
                <label for="interests">Interests:</label>
                <textarea name="interests" id="interests"></textarea>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
            </div>
            <button type="submit" class="action-button">Sign Up</button>
        </form>
    </div>

    <script>
        function validateForm() {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;

            if (username.length < 3) {
                alert('Username must be at least 3 characters long.');
                return false;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }

            const passwordPattern = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.{8,})/;
            if (!passwordPattern.test(password)) {
                alert('Password must be at least 8 characters long, contain at least one uppercase letter, and one special character.');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return false;
            }

            if (firstName.trim() === '' || lastName.trim() === '') {
                alert('First Name and Last Name cannot be empty.');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>


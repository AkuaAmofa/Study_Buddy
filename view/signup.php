<?php
session_start();
require_once '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = get_db_connection();
        
        // Get and sanitize input data
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $firstName = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING));
        $lastName = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING));
        $password = $_POST['password'];

        // Validate input data
        if (empty($username) || empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
            throw new Exception('All fields are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        // Check if username already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Username already exists');
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Email already registered');
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, first_name, last_name) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $username,
            $email,
            $hashedPassword,
            $firstName,
            $lastName
        ]);

        // Set success message in session
        $_SESSION['registration_success'] = true;
        $_SESSION['message'] = 'Registration successful! Please login.';
        
        // Redirect to login page
        header('Location: login.php');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: Please try again later';
        error_log("Database error in signup: " . $e->getMessage());
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Buddy - Sign Up</title>
    <link rel="stylesheet" href="../css/signup.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
   
</head>
<body>
    <section class="signup-section">
        <h1>Create Your Account</h1>
        <form id="register" class="input" method="POST" action="signup.php">
            <input type="text" class="input-place" name="username" placeholder="Username" required>
            <input type="email" class="input-place" name="email" placeholder="Email" required>
            <input type="text" class="input-place" name="first_name" placeholder="First Name" required>
            <input type="text" class="input-place" name="last_name" placeholder="Last Name" required>
            <input type="password" class="input-place" name="password" placeholder="Password" required>
            <input type="password" class="input-place" name="confirmPassword" placeholder="Confirm Password" required>
            <button type="submit" class="sumbit-btn">Sign Up</button>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message" style="color: #dc3545;">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
        </form>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        document.getElementById('register').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('.sumbit-btn');
            const errorDiv = document.getElementById('registerError');
            
            // Show loading state
            submitButton.textContent = 'Creating Account...';
            submitButton.disabled = true;

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    errorDiv.style.color = '#28a745';
                    errorDiv.textContent = data.message;
                    
                    // Redirect to login page
                    window.location.href = data.redirect;
                } else {
                    // Show error message
                    errorDiv.style.color = '#dc3545';
                    errorDiv.textContent = data.message;
                    submitButton.disabled = false;
                    submitButton.textContent = 'Sign Up';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.style.color = '#dc3545';
                errorDiv.textContent = 'An error occurred. Please try again.';
                submitButton.disabled = false;
                submitButton.textContent = 'Sign Up';
            });
        });
    </script>
</body>
</html>

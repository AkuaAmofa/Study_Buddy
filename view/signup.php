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
            INSERT INTO users (username, email, password, first_name, last_name, major, interests)
            VALUES (:username, :email, :password, :first_name, :last_name, :major, :interests)
        ");
        $stmt->execute([
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'major' => $_POST['major'],
            'interests' => $_POST['interests']
        ]);

        // Set success message and redirect to login page
        $_SESSION['signup_success'] = "Registration successful! Please login to continue.";
        header('Location: login.php');
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
    <title>Sign Up - Study Buddy</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="sidebar">
        <!-- Sidebar content here -->
    </div>

    <div class="main-content">
        <div class="dashboard">
            <div class="dede">
                <div class="form">
                    <div class="press">
                        <button class="header-btn active" id="signupBtn">Sign Up</button>
                        <div id="btn"></div>
                    </div>
                    <form id="SignUp" class="input" method="POST" action="signup.php" onsubmit="return validateForm();">
                        <input type="text" class="input-place" name="username" placeholder="Username" required>
                        <input type="email" class="input-place" name="email" placeholder="Email" required>
                        <input type="text" class="input-place" name="first_name" placeholder="First Name" required>
                        <input type="text" class="input-place" name="last_name" placeholder="Last Name" required>
                        <input type="password" class="input-place" name="password" placeholder="Password" required>
                        <input type="password" class="input-place" name="confirm_password" placeholder="Confirm Password" required>
                        <input type="text" class="input-place" name="major" placeholder="Major">
                        <textarea class="input-place" name="interests" placeholder="Interests"></textarea>
                        <button type="submit" class="sumbit-btn">Sign Up</button>
                        <div class="error-message" id="errorMessage">
                            <?php if (!empty($error_message)): ?>
                                <?php echo htmlspecialchars($error_message); ?>
                            <?php endif; ?>
                        </div>
                    </form>
                    <div class="form-links">
                        <a href="login.php">Already have an account? Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/signup.js" defer></script>
</body>
</html>


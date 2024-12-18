<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
    header('Content-Type: application/json');
    
    try {
        $conn = get_db_connection();
        
        $loginInput = trim($_POST['loginInput']);
        $password = $_POST['password'];

        if (empty($loginInput) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        $query = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ?
            "SELECT user_id, username, email, password, is_admin FROM users WHERE email = :loginInput" :
            "SELECT user_id, username, email, password, is_admin FROM users WHERE username = :loginInput";

        $stmt = $conn->prepare($query);
        $stmt->execute(['loginInput' => $loginInput]);

        if ($stmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid email/username or password.']);
            exit;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            
            $redirect = $_SESSION['is_admin'] ? '/admin/dashboard.php' : '/home.php';
            
            echo json_encode([
                'success' => true,
                'redirect' => $redirect,
                'is_admin' => (bool)$user['is_admin']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email/username or password.']);
            exit;
        }
    } catch (Exception $e) {
        log_message("Login error: " . $e->getMessage(), 'ERROR');
        echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again later.']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Study Buddy</title>
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
                        <button class="header-btn active" id="loginBtn">Login</button>
                        <div id="btn"></div>
                    </div>
                    <form id="LogIn" class="input" method="POST">
                        <?php if (isset($_SESSION['signup_success'])): ?>
                            <div class="success-message">
                                <?php 
                                echo htmlspecialchars($_SESSION['signup_success']); 
                                unset($_SESSION['signup_success']); 
                                ?>
                            </div>
                        <?php endif; ?>
                        <input type="text" class="input-place" name="loginInput" placeholder="Email or Username" required>
                        <input type="password" class="input-place" name="password" placeholder="Password" required>
                        <button type="submit" class="sumbit-btn">Login</button>
                        <div class="error-message" id="errorMessage"></div>
                    </form>
                    <div class="form-links">
                        <a href="signup.php">Don't have an account? Sign up here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/login.js" defer></script>
    <script src="js/login.js" defer></script>
</body>
</html>
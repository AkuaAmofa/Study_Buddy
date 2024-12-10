<?php
require_once '../db/db.php';
require_once '../db/logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    log_message("POST data received: " . print_r($_POST, true), 'DEBUG');
    
    try {
        $conn = get_db_connection();
        
        $loginInput = trim($_POST['loginInput']);
        $password = $_POST['password'];

        log_message("Login Input: " . $loginInput, 'DEBUG');
        log_message("Password received: " . ($password ? 'yes' : 'no'), 'DEBUG');

        if (empty($loginInput) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $query = "SELECT username, email, password FROM Users WHERE email = :loginInput";
            log_message("Searching by email", 'DEBUG');
        } else {
            $query = "SELECT username, email, password FROM Users WHERE username = :loginInput";
            log_message("Searching by username", 'DEBUG');
        }

        $stmt = $conn->prepare($query);
        $stmt->execute(['loginInput' => $loginInput]);

        log_message("Query executed. Row count: " . $stmt->rowCount(), 'DEBUG');

        if ($stmt->rowCount() == 0) {
            log_message("No user found for input: $loginInput", 'WARNING');
            echo json_encode(['success' => false, 'message' => 'Invalid email/username or password.']);
            exit;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        log_message("User found: " . print_r(['username' => $user['username'], 'email' => $user['email']], true), 'DEBUG');

        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Get user_id from database and store in session
            $user_id_query = "SELECT user_id FROM Users WHERE username = :username";
            $stmt = $conn->prepare($user_id_query);
            $stmt->execute(['username' => $user['username']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id'] = $user_data['user_id'];
            
            log_message("Login successful for user: " . $user['username'], 'INFO');
            echo json_encode(['success' => true, 'redirect' => 'createprofile.php']);
        } else {
            log_message("Password verification failed for user: " . $user['username'], 'WARNING');
            echo json_encode(['success' => false, 'message' => 'Invalid email/username or password.']);
        }
        
    } catch (Exception $e) {
        log_message("Login error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'success' => false, 
            'message' => 'A server error occurred. Please try again later.',
            'debug' => $e->getMessage()
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            width: 70%;
            max-width: 500px;
            border-radius: 5px;
        }

        .close-modal {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }

        .signup-section {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group button {
            width: 100%;
            padding: 0.7rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .signup-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #007bff;
            text-decoration: none;
        }

        .signup-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-section">
        <form id="loginForm">
            <h2 style="text-align: center;">Login</h2>
            <div class="form-group">
                <label for="loginInput">Email or Username:</label>
                <input type="text" id="loginInput" name="loginInput" placeholder="Enter your email or username" required>
                <div class="error-message" id="loginError"></div>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <div class="error-message" id="passwordError"></div>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
            <a class="signup-link" href="signup.php">Don't have an account? Sign up here</a>
            <div class="error-message" id="errorMessage"></div>
        </form>
    </div>

    <script>
   document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    console.log('Form submitted');

    const formData = new FormData(this);
    const errorMessage = document.getElementById('errorMessage');
    
    // Debug: Log form data
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    errorMessage.textContent = '';

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Parsed data:', data);
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                errorMessage.textContent = data.message || 'Login failed';
            }
        } catch (e) {
            console.error('Parse error:', e);
            console.error('Raw response:', text);
            errorMessage.textContent = 'Server response error. Please try again.';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        errorMessage.textContent = 'An error occurred. Please try again.';
    });
});
    </script>
</body>
</html>

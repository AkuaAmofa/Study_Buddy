<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require '../db/db.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'errors' => []];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    try {
        // Validate required fields
        $required_fields = ['username', 'email', 'first_name', 'last_name', 'password', 'confirmPassword'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $response['errors'][$field] = ucfirst($field) . ' is required';
            }
        }

        if (!empty($response['errors'])) {
            throw new Exception('Please fill in all required fields');
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Password validation with specific error messages
        $password_errors = [];
        if (strlen($password) < 8) {
            $password_errors[] = 'Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $password_errors[] = 'Password must include at least one uppercase letter';
        }
        if (!preg_match('/[\W_]/', $password)) {
            $password_errors[] = 'Password must include at least one special character';
        }
        
        if (!empty($password_errors)) {
            $response['errors']['password'] = $password_errors;
            throw new Exception('Password requirements not met');
        }

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email OR username = :username");
        $stmt->execute(['email' => $email, 'username' => $username]);

        if ($stmt->rowCount() > 0) {
            throw new Exception('User already exists with this email or username.');
        }

        // Hash password and insert user
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO Users (username, email, password, first_name, last_name) 
                              VALUES (:username, :email, :password, :first_name, :last_name)");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $first_name,
            'last_name' => $last_name
        ]);

        $response['success'] = true;
        $response['message'] = 'Registration successful! Redirecting to login...';
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ';
        // Provide specific database error messages
        switch ($e->getCode()) {
            case '23000':
                $response['message'] .= 'Username or email already exists';
                break;
            default:
                $response['message'] .= 'An error occurred while processing your request';
        }
        $response['debug'] = $e->getMessage(); // Only in development
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

// Ensure proper JSON response headers
header('Content-Type: application/json');
echo json_encode($response);
exit(); 
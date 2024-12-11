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
<?php
// signup.php
require_once '../db/db.php'; // Fix the path and use require_once
$conn = get_db_connection(); // Get the database connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate password length and complexity
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[\W_]/', $password)) {
        die('Password must be at least 8 characters long, include at least one uppercase letter, and one special character.');
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        die('Passwords do not match.');
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Prepare SQL statement to check if user exists
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email OR username = :username");
    $stmt->execute(['email' => $email, 'username' => $username]);

    if ($stmt->rowCount() > 0) {
        die('User already exists with this email or username.');
    }

    // Insert new user into the Users table
    $stmt = $conn->prepare("INSERT INTO Users (username, email, password, first_name, last_name) 
                            VALUES (:username, :email, :password, :first_name, :last_name)");
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'first_name' => $first_name,
        'last_name' => $last_name
    ]);

    // Redirect to login page after successful sign-up
    header("Location: login.php");  // Redirect to login page
    exit();
}
?>

    <section class="signup-section">
        <h1>Create Your Account</h1>
        <form action="signup.php" method="POST" id="signupForm">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit">Sign Up</button>
        </form>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>
</html>



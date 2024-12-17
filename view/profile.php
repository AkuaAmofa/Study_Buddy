<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';
$user_data = [];

try {
    $conn = get_db_connection();
    
    // Fetch user data
    $stmt = $conn->prepare("
        SELECT 
            username, 
            email, 
            first_name, 
            last_name, 
            major, 
            interests 
        FROM users 
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update user data
        $update_stmt = $conn->prepare("
            UPDATE users SET 
                first_name = :first_name, 
                last_name = :last_name, 
                major = :major, 
                interests = :interests 
            WHERE user_id = :user_id
        ");
        $update_stmt->execute([
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'major' => $_POST['major'],
            'interests' => $_POST['interests'],
            'user_id' => $_SESSION['user_id']
        ]);

        $success_message = "Profile updated successfully.";
        
        // Refresh user data
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $error_message = "An error occurred while loading or updating the profile: " . $e->getMessage();
    log_message("Error in profile page: " . $e->getMessage(), 'ERROR');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h1>User Profile</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user_data['first_name']); ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user_data['last_name']); ?>">
            </div>
            <div class="form-group">
                <label for="major">Major:</label>
                <input type="text" name="major" id="major" value="<?php echo htmlspecialchars($user_data['major']); ?>">
            </div>
            <div class="form-group">
                <label for="interests">Interests:</label>
                <textarea name="interests" id="interests"><?php echo htmlspecialchars($user_data['interests']); ?></textarea>
            </div>
            <button type="submit" class="action-button">Update Profile</button>
        </form>
    </div>
</body>
</html> 
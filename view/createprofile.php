<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = get_db_connection();
        
        $user_id = $_SESSION['user_id'];
        $major = trim($_POST['major']);
        $interests = trim($_POST['interests']);
        
        // Handle profile picture upload
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $target_dir = '../uploads/profile_pictures/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_info = pathinfo($_FILES['profile_picture']['name']);
            $extension = strtolower($file_info['extension']);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($extension, $allowed_extensions)) {
                $profile_picture = $target_dir . uniqid() . '.' . $extension;
                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
                    $profile_picture = null;
                }
            } else {
                echo 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
                exit;
            }
        }

        // Update profile using prepared statements
        $stmt = $conn->prepare("UPDATE users SET major = ?, interests = ?, profile_picture = ? WHERE user_id = ?");
        $stmt->execute([
            $major,
            $interests,
            $profile_picture,
            $user_id
        ]);

        if ($stmt->execute()) {
            header('Location: home.php');
            exit;
        } else {
            echo 'Error updating profile: ' . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        log_message("Error updating profile: " . $e->getMessage(), 'ERROR');
        echo 'Error updating profile: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Profile</title>
    <style>
        .form-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group button {
            padding: 0.7rem 1.5rem;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Your Profile</h2>
        <form action="createprofile.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="major">Major:</label>
        <input type="text" id="major" name="major" placeholder="Enter your major" required>
    </div>
    <div class="form-group">
        <label for="interests">Interests:</label>
        <textarea id="interests" name="interests" placeholder="Enter your interests" required></textarea>
    </div>
    <div class="form-group">
        <label for="profile_picture">Profile Picture:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
    </div>
    <div class="form-group">
        <button type="submit">Save Profile</button>
    </div>
</form>

    </div>
</body>
</html>

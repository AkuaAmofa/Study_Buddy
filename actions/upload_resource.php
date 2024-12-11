<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    // Validate form data
    if (!isset($_POST['title']) || !isset($_POST['category']) || !isset($_POST['description']) || !isset($_FILES['file'])) {
        throw new Exception('Missing required fields');
    }

    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $file = $_FILES['file'];

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }

    // Create upload directory if it doesn't exist
    $upload_dir = '../uploads/resources/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    // Validate file type
    $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Invalid file type');
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('Failed to save file');
    }

    // Save to database
    $conn = get_db_connection();
    $stmt = $conn->prepare("
        INSERT INTO resources (user_id, title, description, category, file_path, file_type)
        VALUES (:user_id, :title, :description, :category, :file_path, :file_type)
    ");

    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'file_path' => $file_name,
        'file_type' => $file_extension
    ]);

    log_message("Resource uploaded successfully: {$title}", 'INFO');
    
    echo json_encode([
        'success' => true,
        'message' => 'Resource uploaded successfully'
    ]);

} catch (Exception $e) {
    log_message("Error uploading resource: " . $e->getMessage(), 'ERROR');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
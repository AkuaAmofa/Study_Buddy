<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Resource ID not specified');
    }

    $resource_id = $_GET['id'];
    $conn = get_db_connection();

    // Fetch resource details
    $stmt = $conn->prepare("
        SELECT title, file_path, file_type 
        FROM resources 
        WHERE resource_id = :resource_id 
        AND user_id = :user_id
    ");

    $stmt->execute([
        'resource_id' => $resource_id,
        'user_id' => $_SESSION['user_id']
    ]);

    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource) {
        throw new Exception('Resource not found');
    }

    $file_path = '../uploads/resources/' . $resource['file_path'];

    if (!file_exists($file_path)) {
        throw new Exception('File not found');
    }

    // Get MIME type
    $mime_type = mime_content_type($file_path);

    // Clean the filename
    $clean_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $resource['title']);
    $file_extension = pathinfo($resource['file_path'], PATHINFO_EXTENSION);
    $download_filename = $clean_filename . '.' . $file_extension;

    // Set headers for download
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $download_filename . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');

    // Clear output buffer
    ob_clean();
    flush();

    // Output file
    readfile($file_path);
    
    // Log the download
    log_message("Resource downloaded: {$resource['title']}", 'INFO');
    exit();

} catch (Exception $e) {
    log_message("Error downloading resource: " . $e->getMessage(), 'ERROR');
    echo "Error: " . $e->getMessage();
    exit();
}
?> 
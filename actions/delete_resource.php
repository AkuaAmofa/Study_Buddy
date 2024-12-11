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
    // Get JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (!isset($data['resource_id'])) {
        throw new Exception('Resource ID not specified');
    }

    $resource_id = $data['resource_id'];
    $conn = get_db_connection();

    // Get file path before deletion
    $stmt = $conn->prepare("
        SELECT file_path 
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

    // Delete from database
    $stmt = $conn->prepare("
        DELETE FROM resources 
        WHERE resource_id = :resource_id 
        AND user_id = :user_id
    ");

    $stmt->execute([
        'resource_id' => $resource_id,
        'user_id' => $_SESSION['user_id']
    ]);

    // Delete file from server
    $file_path = '../uploads/resources/' . $resource['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    log_message("Resource deleted successfully: ID {$resource_id}", 'INFO');
    
    echo json_encode([
        'success' => true,
        'message' => 'Resource deleted successfully'
    ]);

} catch (Exception $e) {
    log_message("Error deleting resource: " . $e->getMessage(), 'ERROR');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
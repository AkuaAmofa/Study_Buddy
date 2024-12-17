<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Resource ID not specified');
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("
        SELECT * FROM resources 
        WHERE resource_id = ? AND user_id = ?
    ");
    
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        throw new Exception('Resource not found');
    }
    
    echo json_encode([
        'success' => true,
        'resource' => $resource
    ]);

} catch (Exception $e) {
    log_message("Error fetching resource preview: " . $e->getMessage(), 'ERROR');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

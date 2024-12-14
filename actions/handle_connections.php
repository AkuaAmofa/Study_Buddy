<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $conn = get_db_connection();
    
    // Check if connection already exists
    $stmt = $conn->prepare("
        SELECT * FROM user_connections 
        WHERE (follower_id = :user_id AND following_id = :target_id)
        OR (follower_id = :target_id AND following_id = :user_id)
    ");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'target_id' => $data['target_user_id']
    ]);
    
    if ($stmt->rowCount() === 0) {
        // Create new connection
        $stmt = $conn->prepare("
            INSERT INTO user_connections (follower_id, following_id, connection_type, status)
            VALUES (:user_id, :target_id, 'friend', 'pending')
        ");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'target_id' => $data['target_user_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Connection request sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Connection already exists']);
    }
    
} catch (Exception $e) {
    log_message("Error handling connection: " . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>

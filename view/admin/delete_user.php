<?php
session_start();
require_once '../../db/db.php';
require_once '../../db/logger.php';

// Check if admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if POST request with user_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $conn = get_db_connection();
    $user_id = (int)$_POST['user_id'];
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete related records first (using the correct column names from your schema)
    
    // Delete assignments
    $stmt = $conn->prepare("DELETE FROM assignments WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete messages
    $stmt = $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR recipient_id = ?");
    $stmt->execute([$user_id, $user_id]);
    
    // Delete resources (if the table exists and has user_id)
    $stmt = $conn->prepare("DELETE FROM resources WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete study buddy connections
    $stmt = $conn->prepare("DELETE FROM studybuddyconnections WHERE user_id1 = ? OR user_id2 = ?");
    $stmt->execute([$user_id, $user_id]);
    
    // Delete user connections
    $stmt = $conn->prepare("DELETE FROM user_connections WHERE follower_id = ? OR following_id = ?");
    $stmt->execute([$user_id, $user_id]);
    
    // Finally delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND NOT is_admin");
    $stmt->execute([$user_id]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    log_message("Error deleting user: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 
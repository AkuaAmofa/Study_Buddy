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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON data
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        if (!isset($data['user_id2'])) {
            throw new Exception('Recipient ID is required');
        }

        $user_id1 = $_SESSION['user_id'];
        $user_id2 = $data['user_id2'];

        // Verify users exist and aren't the same person
        if ($user_id1 == $user_id2) {
            throw new Exception('Cannot send request to yourself');
        }

        $conn = get_db_connection();

        // Check if connection already exists
        $stmt = $conn->prepare("
            SELECT status 
            FROM studybuddyconnections 
            WHERE (user_id1 = :user_id1 AND user_id2 = :user_id2)
            OR (user_id1 = :user_id2 AND user_id2 = :user_id1)
        ");
        $stmt->execute([
            'user_id1' => $user_id1,
            'user_id2' => $user_id2
        ]);
        
        if ($stmt->fetch()) {
            throw new Exception('Connection already exists');
        }

        // Create new connection request
        $stmt = $conn->prepare("
            INSERT INTO studybuddyconnections (user_id1, user_id2, status, created_at)
            VALUES (:user_id1, :user_id2, 'Pending', NOW())
        ");
        
        $stmt->execute([
            'user_id1' => $user_id1,
            'user_id2' => $user_id2
        ]);

        log_message("Study buddy request sent from user $user_id1 to user $user_id2", 'INFO');
        
        echo json_encode(['success' => true, 'message' => 'Request sent successfully']);

    } catch (Exception $e) {
        log_message("Error sending study buddy request: " . $e->getMessage(), 'ERROR');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
} 
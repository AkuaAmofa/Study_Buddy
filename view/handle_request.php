<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        if (!isset($data['user_id']) || !isset($data['action'])) {
            throw new Exception('Missing required fields');
        }

        $conn = get_db_connection();
        
        if ($data['action'] === 'accept') {
            // Update connection status to Accepted
            $stmt = $conn->prepare("
                UPDATE studybuddyconnections 
                SET status = 'Accepted' 
                WHERE (user_id1 = ? AND user_id2 = ?)
                OR (user_id1 = ? AND user_id2 = ?)
                AND status = 'Pending'
            ");
            $stmt->execute([
                $data['user_id'], $_SESSION['user_id'],
                $_SESSION['user_id'], $data['user_id']
            ]);
            
            $message = 'Connection accepted';
        } elseif ($data['action'] === 'reject') {
            // Update connection status to Rejected
            $stmt = $conn->prepare("
                UPDATE studybuddyconnections 
                SET status = 'Rejected' 
                WHERE (user_id1 = ? AND user_id2 = ?)
                OR (user_id1 = ? AND user_id2 = ?)
                AND status = 'Pending'
            ");
            $stmt->execute([
                $data['user_id'], $_SESSION['user_id'],
                $_SESSION['user_id'], $data['user_id']
            ]);
            
            $message = 'Connection rejected';
        }

        echo json_encode(['success' => true, 'message' => $message]);

    } catch (Exception $e) {
        log_message("Error handling connection request: " . $e->getMessage(), 'ERROR');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}
?>
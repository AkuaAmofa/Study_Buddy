<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        if (!isset($data['user_id']) || !isset($data['action'])) {
            throw new Exception('Missing required parameters');
        }

        $conn = get_db_connection();
        
        if ($data['action'] === 'accept') {
            // Update the connection status to Accepted and set matched_at
            $stmt = $conn->prepare("
                UPDATE studybuddyconnections 
                SET status = 'Accepted',
                    matched_at = CURRENT_TIMESTAMP
                WHERE (user_id1 = ? AND user_id2 = ? OR user_id2 = ? AND user_id1 = ?)
                AND status = 'Pending'
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $data['user_id'],
                $_SESSION['user_id'],
                $data['user_id']
            ]);

            // Add debug logging
            log_message("Updating connection status: user1={$_SESSION['user_id']}, user2={$data['user_id']}", 'INFO');
            log_message("Rows affected: " . $stmt->rowCount(), 'INFO');

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Connection accepted']);
            } else {
                throw new Exception('No pending request found');
            }
        } elseif ($data['action'] === 'reject') {
            // Handle rejection
            $stmt = $conn->prepare("
                UPDATE studybuddyconnections 
                SET status = 'Rejected'
                WHERE (user_id1 = ? AND user_id2 = ? OR user_id2 = ? AND user_id1 = ?)
                AND status = 'Pending'
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $data['user_id'],
                $_SESSION['user_id'],
                $data['user_id']
            ]);

            echo json_encode(['success' => true, 'message' => 'Connection rejected']);
        }

    } catch (Exception $e) {
        log_message("Error handling buddy request: " . $e->getMessage(), 'ERROR');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit();
?>
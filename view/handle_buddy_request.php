<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON data
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        $user_id = $_SESSION['user_id'];

        if (!isset($data['connection_id']) || !isset($data['action'])) {
            throw new Exception('Missing required fields');
        }

        $connection_id = $data['connection_id'];
        $action = $data['action'];

        if (!in_array($action, ['accept', 'reject'])) {
            throw new Exception('Invalid action');
        }

        $conn = get_db_connection();

        // Verify request belongs to user and get connection details
        $stmt = $conn->prepare("
        SELECT connection_id, user_id1, user_id2 
        FROM studybuddyconnections 
        WHERE connection_id = :connection_id
        AND user_id2 = :user_id 
        AND status = 'Pending'
    ");
        
        $stmt->execute([
            'connection_id' => $connection_id,
            'user_id' => $user_id
        ]);
        
        $connection = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$connection) {
            throw new Exception('Request not found or already processed');
        }

        // Update connection status
        $new_status = $action === 'accept' ? 'Accepted' : 'Rejected';
        $stmt = $conn->prepare("
            UPDATE studybuddyconnections 
            SET status = :new_status,
                matched_at = CASE WHEN status = 'Accepted' THEN NOW() ELSE NULL END
            WHERE connection_id = :connection_id
        ");
        
        $stmt->execute([
            'new_status' => $new_status,
            'connection_id' => $connection_id
        ]);

        log_message("Study buddy request {$new_status} for connection ID: {$connection_id}", 'INFO');
        
        echo json_encode(['success' => true, 'message' => "Request {$action}ed successfully"]);

    } catch (Exception $e) {
        log_message("Error handling buddy request: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

echo json_encode([
    'success' => false, 
    'message' => 'Invalid request method'
]);
exit();
?> 
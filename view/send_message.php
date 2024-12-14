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

        if (!isset($data['recipient_id']) || !isset($data['message'])) {
            throw new Exception('Missing required fields');
        }

        $sender_id = $_SESSION['user_id'];
        $recipient_id = $data['recipient_id'];
        $message = trim($data['message']);

        if (empty($message)) {
            throw new Exception('Message cannot be empty');
        }

        $conn = get_db_connection();

        // Verify users are connected
        $stmt = $conn->prepare("
            SELECT connection_id 
            FROM studybuddyconnections 
            WHERE ((user_id1 = :user_id1 AND user_id2 = :user_id2)
            OR (user_id1 = :user_id2 AND user_id2 = :user_id1))
            AND status = 'Accepted'
        ");
        
        $stmt->execute([
            'user_id1' => $sender_id,
            'user_id2' => $recipient_id
        ]);

        if (!$stmt->fetch()) {
            throw new Exception('Cannot send message: Users are not connected');
        }

        // Insert message
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, recipient_id, message_text, sent_at)
            VALUES (:sender_id, :recipient_id, :message, NOW())
        ");

        $stmt->execute([
            'sender_id' => $sender_id,
            'recipient_id' => $recipient_id,
            'message' => $message
        ]);

        log_message("Message sent from user $sender_id to user $recipient_id", 'INFO');

        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        log_message("Error sending message: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
} 
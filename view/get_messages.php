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
    if (!isset($_GET['partner_id'])) {
        throw new Exception('Chat partner not specified');
    }

    $user_id = $_SESSION['user_id'];
    $partner_id = $_GET['partner_id'];
    $last_message_id = isset($_GET['last_id']) ? $_GET['last_id'] : 0;

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
        'user_id1' => $user_id,
        'user_id2' => $partner_id
    ]);

    if (!$stmt->fetch()) {
        throw new Exception('Cannot fetch messages: Users are not connected');
    }

    // Fetch new messages
    $stmt = $conn->prepare("
        SELECT 
            message_id,
            sender_id,
            message_text,
            sent_at,
            CASE 
                WHEN sender_id = :user_id THEN 'sent'
                ELSE 'received'
            END as type
        FROM messages 
        WHERE message_id > :last_id
        AND ((sender_id = :user_id AND recipient_id = :partner_id)
        OR (sender_id = :partner_id AND recipient_id = :user_id))
        ORDER BY sent_at ASC
    ");

    $stmt->execute([
        'user_id' => $user_id,
        'partner_id' => $partner_id,
        'last_id' => $last_message_id
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark messages as read
    if (!empty($messages)) {
        $stmt = $conn->prepare("
            UPDATE messages 
            SET read_at = NOW()
            WHERE recipient_id = :user_id 
            AND sender_id = :partner_id 
            AND read_at IS NULL
        ");
        
        $stmt->execute([
            'user_id' => $user_id,
            'partner_id' => $partner_id
        ]);
    }

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);

} catch (Exception $e) {
    log_message("Error fetching messages: " . $e->getMessage(), 'ERROR');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verify chat partner exists and is connected
try {
    if (!isset($_GET['user_id'])) {
        throw new Exception('Chat partner not specified');
    }

    $partner_id = $_GET['user_id'];
    $conn = get_db_connection();

    // Verify connection exists and is accepted
    $stmt = $conn->prepare("
        SELECT u.username, u.profile_picture
        FROM users u
        JOIN studybuddyconnections sbc 
        ON (sbc.user_id1 = u.user_id OR sbc.user_id2 = u.user_id)
        WHERE u.user_id = :partner_id
        AND ((sbc.user_id1 = :user_id AND sbc.user_id2 = :partner_id)
        OR (sbc.user_id1 = :partner_id AND sbc.user_id2 = :user_id))
        AND sbc.status = 'Accepted'
    ");
    
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'partner_id' => $partner_id
    ]);
    
    $chat_partner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$chat_partner) {
        throw new Exception('Invalid chat partner or not connected');
    }

} catch (Exception $e) {
    log_message("Chat error: " . $e->getMessage(), 'ERROR');
    header('Location: study-buddies.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($chat_partner['username']); ?></title>
    <style>
        /* Add your chat UI styles here */
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 15px;
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
        }
        .message.sent {
            background: #007bff;
            color: white;
            margin-left: 20%;
        }
        .message.received {
            background: #e9ecef;
            margin-right: 20%;
        }
        .chat-input {
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .chat-input form {
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .chat-input button {
            padding: 8px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2>Chat with <?php echo htmlspecialchars($chat_partner['username']); ?></h2>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <!-- Messages will be loaded here -->
        </div>
        
        <div class="chat-input">
            <form id="message-form">
                <input type="text" id="message-input" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <script>
        // Add real-time chat functionality using WebSocket or periodic AJAX calls
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const messagesContainer = document.getElementById('chat-messages');
        const partnerId = <?php echo $partner_id; ?>;

        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (message) {
                sendMessage(message);
                messageInput.value = '';
            }
        });

        function sendMessage(message) {
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipient_id: partnerId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    appendMessage(message, 'sent');
                } else {
                    alert('Error sending message: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending message');
            });
        }

        function appendMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Poll for new messages every few seconds
        setInterval(loadNewMessages, 3000);

        function loadNewMessages() {
            fetch(`get_messages.php?partner_id=${partnerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.messages.forEach(msg => {
                        appendMessage(msg.message, msg.type);
                    });
                }
            })
            .catch(error => console.error('Error loading messages:', error));
        }
    </script>
</body>
</html> 
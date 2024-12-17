<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pending_requests = [];
$new_connections = [];
$error_message = '';

try {
    $conn = get_db_connection();
    
    // Fetch pending study buddy requests
    $stmt = $conn->prepare("
    SELECT 
        sbc.connection_id,
        u.user_id,
        u.username,
        u.first_name,
        u.last_name,
        u.major,
        sbc.matched_at
    FROM studybuddyconnections sbc
    JOIN users u ON u.user_id = sbc.user_id1
    WHERE sbc.user_id2 = :user_id
    AND sbc.status = 'Pending'
    ORDER BY sbc.matched_at DESC
");

    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch recent accepted connections (last 24 hours)
    $stmt = $conn->prepare("
        SELECT 
            sbc.connection_id,
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            sbc.matched_at
        FROM studybuddyconnections sbc
        JOIN users u ON (u.user_id = CASE 
            WHEN sbc.user_id1 = :user_id1 THEN sbc.user_id2
            ELSE sbc.user_id1 END)
        WHERE (sbc.user_id1 = :user_id2 OR sbc.user_id2 = :user_id3)
        AND sbc.status = 'Accepted'
        AND sbc.matched_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([
        'user_id1' => $_SESSION['user_id'],
        'user_id2' => $_SESSION['user_id'],
        'user_id3' => $_SESSION['user_id']
    ]);
    $new_connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = "An error occurred: " . $e->getMessage();
    log_message("Error in notifications: " . $e->getMessage(), 'ERROR');
}

// Set page title and current page for layout
$page_title = 'Notifications';
$current_page = 'notifications';

ob_start();
?>

<div class="notifications-container">
    <h1><i class='bx bx-bell'></i> Notifications</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Pending Study Buddy Requests -->
    <section class="notification-section">
        <h2>Pending Study Buddy Requests</h2>
        <?php if (empty($pending_requests)): ?>
            <p class="empty-state">No pending requests</p>
        <?php else: ?>
            <div class="requests-list">
                <?php foreach ($pending_requests as $request): ?>
                    <div class="request-card">
                        <div class="request-info">
                            <p>
                                <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                            </p>
                            <p><?php echo htmlspecialchars($request['major']); ?></p>
                            <p class="timestamp">Requested <?php echo timeAgo($request['matched_at']); ?></p>
                        </div>
                        <div class="request-actions">
                            <button onclick="handleRequest('<?php echo $request['connection_id']; ?>', 'accept')" 
                                    class="accept-btn">Accept</button>
                            <button onclick="handleRequest('<?php echo $request['connection_id']; ?>', 'reject')" 
                                    class="reject-btn">Decline</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Recent Connections -->
    <section class="notification-section">
        <h2>Recent Connections</h2>
        <?php if (empty($new_connections)): ?>
            <p class="empty-state">No recent connections</p>
        <?php else: ?>
            <div class="connections-list">
                <?php foreach ($new_connections as $connection): ?>
                    <div class="connection-card">
                        <p>
                            <strong><?php echo htmlspecialchars($connection['first_name'] . ' ' . $connection['last_name']); ?></strong>
                            accepted your study buddy request
                            <span class="timestamp"><?php echo timeAgo($connection['matched_at']); ?></span>
                        </p>
                        <p class="email-info">
                            <i class='bx bx-envelope'></i>
                            <span style="cursor: pointer;" onclick="window.location.href='mailto:<?php echo htmlspecialchars($connection['email']); ?>?subject=Study%20Buddy%20Connection';"><?php echo htmlspecialchars($connection['email']); ?></span>
                        </p>
                        <p class="connection-message">
                            <i class='bx bx-check-circle'></i>
                            You can now connect with this user via email
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
.notifications-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 1rem;
}

.notification-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.request-card, .connection-card {
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.request-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.accept-btn, .reject-btn, .chat-btn {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.accept-btn {
    background: #2196F3;
    color: white;
}

.reject-btn {
    background: #dc2626;
    color: white;
}

.timestamp {
    color: #666;
    font-size: 0.875rem;
}

.empty-state {
    color: #666;
    text-align: center;
    padding: 2rem;
}

.email-info {
    color: #666;
    font-size: 0.9rem;
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.email-info i {
    color: #2196F3;
}

.connection-message {
    color: #4CAF50;
    font-size: 0.9rem;
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.connection-message i {
    font-size: 1.1rem;
}
</style>

<script>
function handleRequest(connectionId, action) {
    fetch('handle_buddy_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            connection_id: connectionId,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.success) {
            location.reload();
        } else {
            // alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while handling the request');
    });
}

function startChat(userId) {
    window.location.href = `chat.php?user_id=${userId}`;
}
</script>

<?php
// Helper function to format time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return "just now";
    } elseif ($difference < 3600) {
        return floor($difference/60) . " minutes ago";
    } elseif ($difference < 86400) {
        return floor($difference/3600) . " hours ago";
    } else {
        return date('M j, Y', $timestamp);
    }
}

$content = ob_get_clean();
require_once 'layouts/main_layout.php';
?> 
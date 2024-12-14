<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Initialize variables
$page_title = 'Study Network';
$current_page = 'study-buddies';
$error_message = '';
$success_message = '';
$recommended_users = []; // Initialize as empty array
$connections = []; // Initialize as empty array

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    
    // Get current user's info
    $stmt = $conn->prepare("
        SELECT major, interests 
        FROM users 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current_user) {  // Check if current user exists
        // Fetch potential study buddies with compatibility scoring
        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.first_name,
                u.last_name,
                u.major,
                u.interests,
                u.profile_picture,
                CASE 
                    WHEN u.major = ? THEN 50
                    ELSE 0 
                END +
                CASE 
                    WHEN u.interests LIKE ? THEN 50
                    ELSE 0 
                END as match_score,
                COALESCE(
                    (SELECT 
                        CONCAT(status, ':', IF(user_id1 = ?, 'true', 'false')) 
                     FROM studybuddyconnections 
                     WHERE (user_id1 = ? AND user_id2 = u.user_id)
                     OR (user_id1 = u.user_id AND user_id2 = ?)
                     ORDER BY matched_at DESC 
                     LIMIT 1
                    ), 'none:false') as connection_info
            FROM users u
            WHERE u.user_id != ?
            AND u.user_id NOT IN (
                SELECT CASE 
                    WHEN user_id1 = ? THEN user_id2
                    ELSE user_id1
                END
                FROM studybuddyconnections
                WHERE (user_id1 = ? OR user_id2 = ?)
                AND status = 'Rejected'
            )
            ORDER BY match_score DESC, RAND()
            LIMIT 12
        ");
        
        $stmt->execute([
            $current_user['major'],
            '%' . $current_user['interests'] . '%',
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $_SESSION['user_id']
        ]);
        $recommended_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process connection_info
        foreach ($recommended_users as &$user) {
            list($status, $is_sender) = explode(':', $user['connection_info']);
            $user['connection_status'] = $status;
            $user['is_sender'] = $is_sender === 'true';
        }

        // Fetch existing connections
        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.first_name,
                u.last_name,
                u.major,
                u.profile_picture,
                u.interests,
                sbc.matched_at
            FROM studybuddyconnections sbc
            JOIN users u ON (
                CASE 
                    WHEN sbc.user_id1 = ? THEN sbc.user_id2
                    ELSE sbc.user_id1
                END = u.user_id
            )
            WHERE (sbc.user_id1 = ? OR sbc.user_id2 = ?)
            AND sbc.status = 'Accepted'
            ORDER BY sbc.matched_at DESC
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['user_id'],
            $_SESSION['user_id']
        ]);
        $connections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error_message = "User information not found.";
    }

} catch (Exception $e) {
    log_message("Error in study buddies page: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading the page.";
}

// Include the layout
$content = ob_start();
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Study Network</h1>
        <p class="subtitle">Connect with fellow students and expand your study circle</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Recommended Study Buddies Section -->
    <section class="content-section">
        <h2><i class='bx bx-user-plus'></i> Recommended Study Buddies</h2>
        <div class="user-grid">
            <?php if (empty($recommended_users)): ?>
                <div class="empty-state">
                    <p>No recommended study buddies available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recommended_users as $user): ?>
                    <div class="user-card">
                        <div class="user-card-header">
                            <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? '../assets/default-profile.png'); ?>" 
                                 alt="Profile" class="user-avatar">
                            <div class="match-score">
                                <?php echo $user['match_score']; ?>% Match
                            </div>
                        </div>
                        <div class="user-card-body">
                            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p class="user-major">
                                <i class='bx bx-book'></i>
                                <?php echo htmlspecialchars($user['major'] ?? 'Major not specified'); ?>
                            </p>
                            <p class="user-interests">
                                <i class='bx bx-star'></i>
                                <?php echo htmlspecialchars($user['interests'] ?? 'Interests not specified'); ?>
                            </p>
                        </div>
                        <div class="user-card-footer">
    <?php if ($user['connection_status'] === 'none'): ?>
        <button onclick="sendBuddyRequest(<?php echo $user['user_id']; ?>)" 
                class="action-button">
            <i class='bx bx-user-plus'></i> Connect
        </button>
    <?php elseif ($user['connection_status'] === 'Pending'): ?>
        <?php if ($user['is_sender']): ?>
            <button class="action-button pending" disabled>
                <i class='bx bx-time'></i> Request Sent
            </button>
        <?php else: ?>
            <div class="request-actions">
                <button onclick="handleRequest(<?php echo $user['user_id']; ?>, 'accept')" 
                        class="action-button accept">
                    <i class='bx bx-check'></i> Accept
                </button>
                <button onclick="handleRequest(<?php echo $user['user_id']; ?>, 'reject')" 
                        class="action-button reject">
                    <i class='bx bx-x'></i> Reject
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Current Connections Section -->
    <section class="content-section">
        <h2><i class='bx bx-group'></i> Your Study Buddies</h2>
        <div class="user-grid">
            <?php if (empty($connections)): ?>
                <div class="empty-state">
                    <p>You haven't connected with any study buddies yet.</p>
                    <p>Start by sending connection requests to fellow students!</p>
                </div>
            <?php else: ?>
                <?php foreach ($connections as $connection): ?>
                    <div class="user-card">
                        <div class="user-card-header">
                            <img src="<?php echo htmlspecialchars($connection['profile_picture'] ?? '../assets/default-profile.png'); ?>" 
                                 alt="Profile" class="user-avatar">
                            <div class="connection-status">
                                <i class='bx bx-check-circle'></i> Connected
                            </div>
                        </div>
                        <div class="user-card-body">
                            <h3><?php echo htmlspecialchars($connection['first_name'] . ' ' . $connection['last_name']); ?></h3>
                            <p class="user-major">
                                <i class='bx bx-book'></i>
                                <?php echo htmlspecialchars($connection['major'] ?? 'Major not specified'); ?>
                            </p>
                            <p class="connection-info">
                                <i class='bx bx-calendar'></i>
                                Connected since: <?php echo date('M d, Y', strtotime($connection['matched_at'])); ?>
                            </p>
                        </div>
                        <div class="user-card-footer">
                            <button onclick="startChat(<?php echo $connection['user_id']; ?>)" 
                                    class="action-button">
                                <i class='bx bx-message-square-dots'></i> Message
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
function sendBuddyRequest(userId) {
    fetch('send_buddy_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id2: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload the page to show updated status
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the request');
    });
}

function handleRequest(userId, action) {
    fetch('handle_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'An error occurred');
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
$content = ob_get_clean();
require_once 'layouts/main_layout.php';
?>

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

// Update the profile picture path to use the correct default image path
$default_profile_img = '/Study_Buddy/assets/images/default-profile.png'; // Adjust this path to match your project structure

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    
    // Debug: Check if connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Simplified connections query
    $conn_stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.major,
            u.interests,
            c.matched_at,
            c.status as connection_status
        FROM users u
        JOIN studybuddyconnections c ON 
            (c.user_id1 = u.user_id AND c.user_id2 = ?)
            OR (c.user_id2 = u.user_id AND c.user_id1 = ?)
        WHERE c.status = 'Accepted'
        AND u.user_id != ?
        ORDER BY c.matched_at DESC
    ");
    
    $conn_stmt->execute([
        $_SESSION['user_id'], 
        $_SESSION['user_id'],
        $_SESSION['user_id']
    ]);
    $connections = $conn_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add debug logging
    error_log("User ID: " . $_SESSION['user_id']);
    error_log("Connections query executed");
    error_log("Number of connections found: " . count($connections));
    foreach ($connections as $connection) {
        error_log("Connection found: " . print_r($connection, true));
    }
    
    // Simplified recommended users query
    $stmt = $conn->prepare("
        SELECT 
            u.user_id, 
            u.username,
            u.first_name, 
            u.last_name, 
            u.email,
            u.major, 
            u.interests
        FROM users u
        WHERE u.user_id != ? 
        AND u.is_admin = 0
        AND u.user_id NOT IN (
            SELECT 
                CASE 
                    WHEN user_id1 = ? THEN user_id2
                    ELSE user_id1
                END
            FROM studybuddyconnections
            WHERE (user_id1 = ? OR user_id2 = ?)
            AND status IN ('Pending', 'Accepted')
        )
        ORDER BY RAND()
        LIMIT 12
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        $_SESSION['user_id']
    ]);
    $recommended_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get pending connections
    $pending_stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.major,
            u.interests,
            u.profile_picture,
            c.status,
            CASE WHEN c.user_id1 = ? THEN 1 ELSE 0 END as is_sender
        FROM users u
        JOIN studybuddyconnections c ON 
            (c.user_id1 = u.user_id AND c.user_id2 = ?)
            OR (c.user_id2 = u.user_id AND c.user_id1 = ?)
        WHERE c.status = 'Pending'
    ");
    
    $pending_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $pending_connections = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Debug Error: " . $e->getMessage();
    log_message("Error in study buddies page: " . $e->getMessage(), 'ERROR');
}

ob_start();
?>

<style>
/* Base variables */
:root {
    --base-spacing: 1rem;
    --small-spacing: 0.5rem;
    --large-spacing: 2rem;
    --text-color: #333;
    --muted-text: #666;
    --primary-color: #2196F3;
    --secondary-color: #1976D2;
    --accent-color: #4CAF50;
    --background-color: #f8f9fa;
    --light-text-color: #fff;
    --card-shadow: 0 2px 8px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

/* Study Network Layout */
.study-network {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--base-spacing);
    background-color: var(--light-text-color);
    border-radius: 16px;
    box-shadow: var(--card-shadow);
}

/* Header Section */
.network-header {
    text-align: center;
    margin-bottom: var(--large-spacing);
}

.network-header h1 {
    font-size: 2rem;
    color: var(--text-color);
    margin-bottom: var(--small-spacing);
    font-weight: 600;
}

.network-header p {
    color: var(--muted-text);
    font-size: 1.1rem;
}

/* Content Sections */
.network-section {
    background: var(--light-text-color);
    padding: var(--large-spacing);
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    margin-bottom: var(--large-spacing);
}

.network-section h2 {
    display: flex;
    align-items: center;
    gap: var(--small-spacing);
    color: var(--text-color);
    font-size: 1.25rem;
    margin-bottom: var(--large-spacing);
    font-weight: 600;
}

.network-section h2 i {
    color: var(--primary-color);
    font-size: 1.5rem;
}

/* User Grid Layout */
.user-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--large-spacing);
}

/* User Cards */
.user-card {
    background: var(--light-text-color);
    border-radius: 12px;
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid var(--background-color);
}

.user-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--card-shadow);
}

.user-card-header {
    position: relative;
    padding: var(--base-spacing);
    text-align: center;
    background: var(--background-color);
}

.user-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--light-text-color);
    box-shadow: var(--card-shadow);
}

.match-score {
    position: absolute;
    top: var(--base-spacing);
    right: var(--base-spacing);
    background: var(--primary-color);
    color: var(--light-text-color);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.user-card-body {
    padding: var(--base-spacing);
}

.user-card-body h3 {
    color: var(--text-color);
    font-weight: 600;
    margin-bottom: var(--small-spacing);
}

.user-info {
    display: flex;
    align-items: center;
    gap: var(--small-spacing);
    color: var(--muted-text);
    font-size: 0.875rem;
    margin-bottom: var(--small-spacing);
}

.user-info i {
    color: var(--primary-color);
}

.user-card-footer {
    padding: var(--base-spacing);
    border-top: 1px solid var(--background-color);
}

/* Connection Status Badges */
.connection-status {
    display: inline-flex;
    align-items: center;
    gap: var(--small-spacing);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-connected {
    background-color: #dcfce7;
    color: #166534;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: var(--large-spacing);
    color: var(--muted-text);
}

.empty-state i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: var(--base-spacing);
}

.empty-state p {
    margin-bottom: var(--base-spacing);
    font-size: 0.875rem;
}

/* Action Buttons */
.connect-button {
    width: 100%;
    padding: var(--base-spacing);
    background-color: var(--primary-color);
    color: var(--light-text-color);
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--small-spacing);
}

.connect-button:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
}

.connect-button.pending {
    background-color: var(--background-color);
    color: var(--muted-text);
    cursor: not-allowed;
}

.connect-button.connected {
    background-color: var(--accent-color);
}

.request-actions {
    display: flex;
    gap: 0.5rem;
}
</style>

<div class="study-network">
    <div class="network-header">
        <h1>Study Network</h1>
        <p class="subtitle">Connect with fellow students and expand your study circle</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="error-message" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Your Study Buddies Section -->
    <section class="network-section">
        <h2><i class='bx bx-group'></i> Your Study Buddies</h2>
        <div class="user-grid">
            <?php if (empty($connections)): ?>
                <div class="empty-state">
                    <i class='bx bx-user-circle'></i>
                    <p>You haven't connected with any study buddies yet.</p>
                    <p>Start by sending connection requests to fellow students!</p>
                </div>
            <?php else: ?>
                <?php foreach ($recommended_users as $user): ?>
                    <div class="user-card">
                        <div class="user-card-body">
                            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <div class="user-info">
                                <i class='bx bx-book'></i>
                                <span><?php echo htmlspecialchars($user['major'] ?? 'Major not specified'); ?></span>
                            </div>
                            <div class="user-info">
                                <i class='bx bx-envelope'></i>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>
                        <div class="user-card-footer">
                            <button onclick="startChat(<?php echo $user['user_id']; ?>)" 
                                    class="connect-button connected">
                                <i class='bx bx-message-square-dots'></i> Message
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recommended Study Buddies Section -->
    <section class="network-section">
        <h2><i class='bx bx-user-plus'></i> Recommended Study Buddies</h2>
        <div class="user-grid">
            <?php if (empty($recommended_users)): ?>
                <div class="empty-state">
                    <i class='bx bx-search'></i>
                    <p>No recommended study buddies available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recommended_users as $user): ?>
                    <div class="user-card">
                        <div class="user-card-body">
                            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <div class="user-info">
                                <i class='bx bx-book'></i>
                                <span><?php echo htmlspecialchars($user['major'] ?? 'Major not specified'); ?></span>
                            </div>
                            <div class="user-info">
                                <i class='bx bx-star'></i>
                                <span><?php echo htmlspecialchars($user['interests'] ?? 'Interests not specified'); ?></span>
                            </div>
                        </div>
                        <div class="user-card-footer">
                            <button onclick="sendBuddyRequest(<?php echo $user['user_id']; ?>)" 
                                    class="connect-button">
                                <i class='bx bx-user-plus'></i> Connect
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

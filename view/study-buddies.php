<?php
require_once '../db/db.php';
require_once '../db/logger.php';

// Set page variables before including main layout
$page_title = 'Study Buddies';
$current_page = 'study-buddies';

// Start output buffering
ob_start();

try {
    $conn = get_db_connection();
    
    // Get current user's profile
    $stmt = $conn->prepare("
        SELECT major, interests 
        FROM users 
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id'] ?? null]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_user) {
        throw new Exception('User profile not found');
    }

    // Fetch potential study buddies with compatibility score
    // Excludes current user and already connected users
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.major,
            u.interests,
            u.profile_picture,
            CASE 
                WHEN u.major = :major THEN 50 
                ELSE 0 
            END + 
            CASE 
                WHEN u.interests LIKE CONCAT('%', :interests, '%') THEN 50 
                ELSE 0 
            END as compatibility_score
        FROM users u
        WHERE u.user_id != :user_id
        AND u.user_id NOT IN (
            SELECT user_id2 FROM studybuddyconnections 
            WHERE user_id1 = :user_id
            UNION
            SELECT user_id1 FROM studybuddyconnections 
            WHERE user_id2 = :user_id
        )
        HAVING compatibility_score > 0
        ORDER BY compatibility_score DESC
        LIMIT 10
    ");
    
    $stmt->execute([
        'user_id' => $_SESSION['user_id'] ?? null,
        'major' => $current_user['major'],
        'interests' => $current_user['interests']
    ]);
    $potential_buddies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending requests
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.major,
            u.profile_picture,
            sbc.status,
            sbc.connection_id
        FROM studybuddyconnections sbc
        JOIN users u ON u.user_id = sbc.user_id1
        WHERE sbc.user_id2 = :user_id 
        AND sbc.status = 'Pending'
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id'] ?? null]);
    $pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch current connections
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.major,
            u.profile_picture,
            sbc.matched_at
        FROM studybuddyconnections sbc
        JOIN users u ON (u.user_id = sbc.user_id1 OR u.user_id = sbc.user_id2)
        WHERE (sbc.user_id1 = :user_id OR sbc.user_id2 = :user_id)
        AND u.user_id != :user_id
        AND sbc.status = 'Accepted'
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id'] ?? null]);
    $current_connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    log_message("Error in study buddies page: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading the page. Please try again later.";
}
?>

<style>
    .container {
        padding: 20px;
    }

    .section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .buddy-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .buddy-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .profile-pic {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 10px;
        display: block;
    }

    .compatibility-score {
        background: #007bff;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        display: inline-block;
        margin-bottom: 10px;
    }

    .action-btn {
        width: 100%;
        padding: 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 10px;
    }

    .connect-btn {
        background-color: #28a745;
        color: white;
    }

    .accept-btn {
        background-color: #007bff;
        color: white;
    }

    .reject-btn {
        background-color: #dc3545;
        color: white;
    }

    .message-btn {
        background-color: #17a2b8;
        color: white;
    }

    .error-message {
        max-width: 1200px;
        margin: 0 auto 20px;
    }
    .empty-state {
        text-align: center;
        padding: 20px;
        color: #666;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 10px 0;
    }
</style>

<div class="container">
    <h1>Study Buddies</h1>

    <!-- Pending Requests Section -->
    <?php if (!empty($pending_requests)): ?>
    <div class="section">
        <h2>Pending Requests</h2>
        <div class="buddy-grid">
            <?php foreach ($pending_requests as $request): ?>
            <div class="buddy-card">
                <img src="<?php echo htmlspecialchars($request['profile_picture'] ?? '../assets/default-profile.png'); ?>" 
                     alt="Profile Picture" class="profile-pic">
                <h3><?php echo htmlspecialchars($request['username']); ?></h3>
                <p>Major: <?php echo htmlspecialchars($request['major']); ?></p>
                <button class="action-btn accept-btn" 
                        onclick="handleRequest(<?php echo $request['connection_id']; ?>, 'accept')">
                    Accept
                </button>
                <button class="action-btn reject-btn" 
                        onclick="handleRequest(<?php echo $request['connection_id']; ?>, 'reject')">
                    Reject
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recommended Study Buddies Section -->
    <div class="section">
        <h2>Recommended Study Buddies</h2>
        <div class="buddy-grid">
            <?php if (empty($potential_buddies)): ?>
                <div class="empty-state">
                    <p>No study buddy recommendations available at this time. Try updating your profile interests!</p>
                </div>
            <?php else: ?>
                <?php foreach ($potential_buddies as $buddy): ?>
                <div class="buddy-card">
                    <img src="<?php echo htmlspecialchars($buddy['profile_picture'] ?? '../assets/default-profile.png'); ?>" 
                         alt="Profile Picture" class="profile-pic">
                    <h3><?php echo htmlspecialchars($buddy['username']); ?></h3>
                    <div class="compatibility-score">
                        <?php echo $buddy['compatibility_score']; ?>% Match
                    </div>
                    <p>Major: <?php echo htmlspecialchars($buddy['major']); ?></p>
                    <p>Interests: <?php echo htmlspecialchars($buddy['interests']); ?></p>
                    <button class="action-btn connect-btn" 
                            onclick="sendRequest(<?php echo $buddy['user_id']; ?>)">
                        Connect
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Current Connections Section -->
    <div class="section">
        <h2>Your Study Buddies</h2>
        <div class="buddy-grid">
            <?php if (empty($current_connections)): ?>
                <div class="empty-state">
                    <p>You haven't connected with any study buddies yet. Try sending some connection requests!</p>
                </div>
            <?php else: ?>
                <?php foreach ($current_connections as $connection): ?>
                <div class="buddy-card">
                    <img src="<?php echo htmlspecialchars($connection['profile_picture'] ?? '../assets/default-profile.png'); ?>" 
                         alt="Profile Picture" class="profile-pic">
                    <h3><?php echo htmlspecialchars($connection['username']); ?></h3>
                    <p>Major: <?php echo htmlspecialchars($connection['major']); ?></p>
                    <p>Connected since: <?php echo date('M d, Y', strtotime($connection['matched_at'])); ?></p>
                    <button class="action-btn message-btn" 
                            onclick="openChat(<?php echo $connection['user_id']; ?>)">
                        Message
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function sendRequest(userId) {
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
                alert('Request sent successfully!');
                location.reload();
            } else {
                alert('Error sending request: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending request');
        });
    }

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
                if (data.success) {
                    alert(action === 'accept' ? 'Request accepted!' : 'Request rejected');
                    location.reload();
                } else {
                    alert('Error handling request: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error handling request');
            });
        }

        function openChat(userId) {
            // Implement chat functionality or redirect to chat page
            window.location.href = `chat.php?user_id=${userId}`;
        }
    </script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the main layout which will use $content
require_once 'layouts/main_layout.php';
?>

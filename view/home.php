<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Initialize variables
$recent_assignments = [];
$recent_connections = [];
$recent_resources = [];
$error_message = '';
$first_name = '';

$page_title = 'Dashboard';
$current_page = 'home';

ob_start();
$content = ob_get_clean();

require_once 'layouts/main_layout.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    
    // Get user's first name
    $stmt = $conn->prepare("
        SELECT first_name 
        FROM users 
        WHERE user_id = :user_id
    ");
    
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $first_name = $user['first_name'] ?? $_SESSION['username'];
    
    // Debug: Log the first name
    log_message("First name fetched: " . $first_name, 'DEBUG');
    
    // Fetch recent assignments
    $stmt = $conn->prepare("
        SELECT 
            assignment_id,
            title,
            course,
            due_date,
            priority,
            status,
            created_at
        FROM assignments 
        WHERE user_id = :user_id 
        ORDER BY 
            CASE 
                WHEN due_date >= CURRENT_DATE THEN 0
                ELSE 1
            END,
            due_date ASC,
            CASE 
                WHEN priority = 'High' THEN 1
                WHEN priority = 'Medium' THEN 2
                WHEN priority = 'Low' THEN 3
            END
        LIMIT 5
    ");
    
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $recent_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch recent connections
    // ... your existing connections query ...

} catch (Exception $e) {
    log_message("Error fetching data: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading the page.";
    $first_name = $_SESSION['username'];
}

// Debug: Check the value before display
log_message("First name before display: " . $first_name, 'DEBUG');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Buddy - Dashboard</title>
    <link rel="stylesheet" href="../css/home.css">


</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">Study Buddy</div>
        <nav class="sidebar-menu">
            <a href="home.php" class="menu-item active">
                <i class='bx bx-home'></i> Home
            </a>
            <a href="assignments.php" class="menu-item">
                <i class='bx bx-task'></i> Assignments
            </a>
            <a href="study-buddies.php" class="menu-item">
                <i class='bx bx-user'></i> Study Buddies
            </a>
            <a href="resources.php" class="menu-item">
                <i class='bx bx-book'></i> Resources
            </a>
            <a href="logout.php" class="menu-item">
                <i class='bx bx-log-out'></i> Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="dashboard">
            <h1>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h1>
            
            <div class="controls">
                <button class="action-button" onclick="window.location.href='assignments.php'">Manage Assignments</button>
                <button class="action-button" onclick="window.location.href='study-buddies.php'">Find Study Buddies</button>
                <button class="action-button" onclick="window.location.href='resources.php'">Access Resources</button>
            </div>

            <div class="split-container">
                <div class="feed-section">
                    <h2>Recent Assignments</h2>
                    <?php if (empty($recent_assignments)): ?>
                        <div class="feed-item empty-state">
                            <p>No assignments yet. Start by creating your first assignment!</p>
                            <a href="assignments.php" class="action-button">Create Assignment</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_assignments as $assignment): ?>
                            <div class="feed-item assignment-card">
                                <div class="assignment-status <?php echo strtolower(str_replace(' ', '-', $assignment['status'])); ?>">
                                    <?php echo htmlspecialchars($assignment['status']); ?>
                                </div>
                                <div class="assignment-content">
                                    <h4><?php echo htmlspecialchars($assignment['title']); ?></h4>
                                    <p class="course"><?php echo htmlspecialchars($assignment['course']); ?></p>
                                    <div class="assignment-details">
                                        <span class="due-date">
                                            Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                                        </span>
                                        <span class="priority <?php echo strtolower($assignment['priority']); ?>">
                                            <?php echo htmlspecialchars($assignment['priority']); ?> Priority
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="view-all">
                            <a href="assignments.php" class="action-button">View All Assignments</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="feed-section">
                    <h2>Study Buddy Activities</h2>
                    <?php if (empty($recent_connections)): ?>
                        <div class="feed-item empty-state">
                            <p>No recent study buddy activities. Start connecting with other students!</p>
                            <a href="study-buddies.php" class="action-button">Find Study Buddies</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_connections as $connection): ?>
                            <div class="feed-item">
                                <img src="<?php echo htmlspecialchars($connection['profile_picture'] ?? '../assets/default-profile.png'); ?>" 
                                     alt="Profile Picture" class="profile-pic">
                                <div class="feed-content">
                                    <h4><?php echo htmlspecialchars($connection['username']); ?></h4>
                                    <?php if ($connection['status'] === 'Pending'): ?>
                                        <p class="status pending">Pending Connection Request</p>
                                        <p class="timestamp">Sent: <?php echo date('M d, Y', strtotime($connection['created_at'])); ?></p>
                                    <?php else: ?>
                                        <p class="status connected">Connected</p>
                                        <p class="timestamp">Connected since: <?php echo date('M d, Y', strtotime($connection['matched_at'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="feed-section">
                    <h2>Recent Resources</h2>
                    <?php if (empty($recent_resources)): ?>
                        <div class="feed-item empty-state">
                            <p>No resources yet. Start by adding study materials!</p>
                            <a href="resources.php" class="action-button">Add Resources</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_resources as $resource): ?>
                            <div class="feed-item">
                                <h4><?php echo htmlspecialchars($resource['title']); ?></h4>
                                <p>Added: <?php echo htmlspecialchars($resource['uploaded_at']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
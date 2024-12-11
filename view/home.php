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
// Your existing home page content here, without the HTML structure
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .nav-button {
            flex: 1;
            margin: 0 10px;
            padding: 20px;
            text-align: center;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: #0056b3;
        }

        .feeds-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .feed-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .feed-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .feed-item:last-child {
            border-bottom: none;
        }

        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .feed-content {
            flex: 1;
        }

        .feed-content h4 {
            margin: 0 0 5px 0;
        }

        .status {
            font-size: 0.9em;
            margin: 0;
        }

        .status.pending {
            color: #f57c00;
        }

        .status.connected {
            color: #43a047;
        }

        .timestamp {
            font-size: 0.8em;
            color: #666;
            margin: 5px 0 0 0;
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .action-button {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        .action-button:hover {
            background: #0056b3;
        }

        h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .assignment-card {
            display: flex;
            gap: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .assignment-status {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
            min-width: 100px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .assignment-status.not.started {
            background-color: #e9ecef;
            color: #495057;
        }

        .assignment-status.in.progress {
            background-color: #fff3cd;
            color: #856404;
        }

        .assignment-status.completed {
            background-color: #d4edda;
            color: #155724;
        }

        .assignment-content {
            flex: 1;
            padding: 10px;
        }

        .assignment-content h4 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .course {
            color: #666;
            font-size: 0.9em;
            margin: 0 0 8px 0;
        }

        .assignment-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85em;
        }

        .due-date {
            color: #666;
        }

        .priority {
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .priority.high {
            background-color: #ffebee;
            color: #c62828;
        }

        .priority.medium {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .priority.low {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .view-all {
            text-align: center;
            margin-top: 15px;
        }

        .feed-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .feed-section h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h1>
        
        <div class="navigation-buttons">
            <a href="assignments.php" class="nav-button">
                <h3>Assignments</h3>
                <p>Manage your assignments</p>
            </a>
            <a href="study-buddies.php" class="nav-button">
                <h3>Study Buddies</h3>
                <p>Connect with study partners</p>
            </a>
            <a href="resources.php" class="nav-button">
                <h3>Resources</h3>
                <p>Access study materials</p>
            </a>
        </div>

        <div class="feeds-container">
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
</body>
</html>

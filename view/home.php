<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Fetch user's recent activities
try {
    $conn = get_db_connection();
    
    // Fetch recent assignments
    $stmt = $conn->prepare("
        SELECT title, due_date, status 
        FROM assignments 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $recent_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch study buddy connections
    $stmt = $conn->prepare("
        SELECT u.username, sbc.status, sbc.matched_at
        FROM studybuddyconnections sbc
        JOIN users u ON (u.user_id = sbc.user_id1 OR u.user_id = sbc.user_id2)
        WHERE (sbc.user_id1 = :user_id OR sbc.user_id2 = :user_id)
        AND u.user_id != :user_id
        ORDER BY sbc.matched_at DESC
        LIMIT 5
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $recent_connections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch recent resources
    $stmt = $conn->prepare("
        SELECT title, uploaded_at 
        FROM resources 
        WHERE user_id = :user_id 
        ORDER BY uploaded_at DESC 
        LIMIT 5
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $recent_resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    log_message("Error fetching dashboard data: " . $e->getMessage(), 'ERROR');
}
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
        }

        .feed-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .feed-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .feed-item:last-child {
            border-bottom: none;
        }

        h2 {
            color: #333;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
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
                <?php foreach ($recent_assignments as $assignment): ?>
                    <div class="feed-item">
                        <h4><?php echo htmlspecialchars($assignment['title']); ?></h4>
                        <p>Due: <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                        <p>Status: <?php echo htmlspecialchars($assignment['status']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="feed-section">
                <h2>Study Buddy Activities</h2>
                <?php foreach ($recent_connections as $connection): ?>
                    <div class="feed-item">
                        <h4>Connected with <?php echo htmlspecialchars($connection['username']); ?></h4>
                        <p>Status: <?php echo htmlspecialchars($connection['status']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="feed-section">
                <h2>Recent Resources</h2>
                <?php foreach ($recent_resources as $resource): ?>
                    <div class="feed-item">
                        <h4><?php echo htmlspecialchars($resource['title']); ?></h4>
                        <p>Added: <?php echo htmlspecialchars($resource['uploaded_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>

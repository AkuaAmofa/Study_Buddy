<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$due_assignments = [];
$new_connections = [];
$error_message = '';

try {
    $conn = get_db_connection();
    
    // Fetch assignments due in 2 days or the day before
    $stmt = $conn->prepare("
        SELECT 
            assignment_id,
            title, 
            due_date 
        FROM assignments 
        WHERE user_id = :user_id 
        AND (due_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY) OR due_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY))
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $due_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch new study buddy connections
    $stmt = $conn->prepare("
        SELECT 
            sbc.connection_id,
            u.username, 
            sbc.matched_at 
        FROM studybuddyconnections sbc
        JOIN users u ON (u.user_id = sbc.user_id1 OR u.user_id = sbc.user_id2)
        WHERE (sbc.user_id1 = :user_id OR sbc.user_id2 = :user_id)
        AND u.user_id != :user_id
        AND sbc.status = 'Accepted'
        AND sbc.matched_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $new_connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = "An error occurred while loading notifications: " . $e->getMessage();
    log_message("Error fetching notifications: " . $e->getMessage(), 'ERROR');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_notifications'])) {
    try {
        // Clear notifications logic here
        // For example, mark notifications as read in the database
        // This is a placeholder for actual implementation
        // $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        // $stmt->execute(['user_id' => $_SESSION['user_id']]);
        
        // Redirect to refresh the page
        header('Location: notifications.php');
        exit();
    } catch (Exception $e) {
        $error_message = "An error occurred while clearing notifications: " . $e->getMessage();
        log_message("Error clearing notifications: " . $e->getMessage(), 'ERROR');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Notifications</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <ul class="notifications-list">
                <?php foreach ($due_assignments as $assignment): ?>
                    <li class="notification-item">
                        <p>Assignment "<?php echo htmlspecialchars($assignment['title']); ?>" is due on <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>.</p>
                    </li>
                <?php endforeach; ?>

                <?php foreach ($new_connections as $connection): ?>
                    <li class="notification-item">
                        <p>You have a new study buddy connection with <?php echo htmlspecialchars($connection['username']); ?> since <?php echo date('M d, Y', strtotime($connection['matched_at'])); ?>.</p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit" name="clear_notifications" class="clear-button">Clear Notifications</button>
        </form>
    </div>
</body>
</html> 
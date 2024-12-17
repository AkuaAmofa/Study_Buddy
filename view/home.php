<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Initialize variables
$page_title = 'Home';
$current_page = 'home';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    
    // Get user's name
    $stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $first_name = $user['first_name'];

    // Get recent assignments
    $stmt = $conn->prepare("
        SELECT * FROM assignments 
        WHERE user_id = ? 
        ORDER BY due_date ASC 
        LIMIT 2
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get count of pending buddy requests
    $stmt = $conn->prepare("
        SELECT COUNT(*) as pending_count
        FROM studybuddyconnections
        WHERE user_id2 = ?
        AND status = 'Pending'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pending_count = $result['pending_count'];

} catch (Exception $e) {
    log_message("Error in home page: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading the dashboard.";
}

ob_start();
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
            <p class="subtitle">Track your progress and connect with study buddies</p>
        </div>
        <div class="notification-badge">
            <a href="notifications.php" class="notification-link">
                <i class='bx bx-bell'></i>
                <?php if ($pending_count > 0): ?>
                    <span class="badge"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="assignments.php" class="action-button">
            <i class='bx bx-list-plus'></i> Manage Assignments
        </a>
        <a href="study-buddies.php" class="action-button">
            <i class='bx bx-user-plus'></i> Find Study Buddies
        </a>
        <a href="resources.php" class="action-button">
            <i class='bx bx-book-open'></i> Access Resources
        </a>
    </div>

    <div class="dashboard-grid">
        <!-- Recent Assignments Section -->
        <div class="dashboard-card">
            <h2>Recent Assignments</h2>
            <?php if (empty($assignments)): ?>
                <p class="empty-state">No assignments yet. Start by adding one!</p>
            <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                    <div class="assignment-item">
                        <div class="assignment-header">
                            <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                            <span class="status <?php echo strtolower($assignment['status']); ?>">
                                <?php echo htmlspecialchars($assignment['status']); ?>
                            </span>
                        </div>
                        <p class="course"><?php echo htmlspecialchars($assignment['course']); ?></p>
                        <p class="due-date">Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></p>
                        <span class="priority <?php echo strtolower($assignment['priority']); ?>">
                            <?php echo htmlspecialchars($assignment['priority']); ?> Priority
                        </span>
                    </div>
                <?php endforeach; ?>
                <a href="assignments.php" class="view-all">View All Assignments</a>
            <?php endif; ?>
        </div>

        <!-- Study Buddy Activities Section -->
        <div class="dashboard-card">
            <h2>Study Buddy Activities</h2>
            <div class="empty-state">
                <p>No recent study buddy activities.</p>
                <p>Start connecting with other students!</p>
                <a href="study-buddies.php" class="action-button">
                    Find Study Buddies
                </a>
            </div>
        </div>

        <!-- Recent Resources Section -->
        <div class="dashboard-card">
            <h2>Recent Resources</h2>
            <div class="empty-state">
                <p>No resources yet. Start by adding study materials!</p>
                <a href="resources.php" class="action-button">
                    Add Resources
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.dashboard-container h1 {
    margin-bottom: 2rem;
    color: #333;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.action-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #2196F3;
    color: white;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-size: 1rem;
    transition: background 0.3s ease;
}

.action-button:hover {
    background: #1976D2;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.dashboard-card h2 {
    color: #333;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
}

.empty-state {
    text-align: center;
    color: #666;
    padding: 2rem 0;
}

.empty-state p {
    margin-bottom: 1rem;
}

.assignment-item {
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.5rem;
}

.assignment-header h3 {
    margin: 0;
    color: #333;
    font-size: 1rem;
}

.status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
}

.status.not-started {
    background: #FFF3E0;
    color: #F57C00;
}

.status.in-progress {
    background: #E3F2FD;
    color: #1976D2;
}

.status.completed {
    background: #E8F5E9;
    color: #388E3C;
}

.course {
    color: #666;
    margin-bottom: 0.5rem;
}

.due-date {
    color: #666;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.priority {
    display: inline-block;
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.priority.low {
    background: #E8F5E9;
    color: #388E3C;
}

.priority.medium {
    background: #FFF3E0;
    color: #F57C00;
}

.priority.high {
    background: #FFEBEE;
    color: #D32F2F;
}

.view-all {
    display: block;
    text-align: center;
    color: #2196F3;
    text-decoration: none;
    margin-top: 1rem;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem;
    }

    .action-buttons {
        flex-direction: column;
    }

    .action-button {
        width: 100%;
        justify-content: center;
    }
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.notification-badge {
    position: relative;
}

.notification-link {
    font-size: 1.5rem;
    color: var(--primary-color);
    text-decoration: none;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.notification-link:hover {
    background-color: var(--background-color);
}

.badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: var(--accent-color);
    color: white;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
    min-width: 1.5rem;
    text-align: center;
}
</style>

<?php
$content = ob_get_clean();
require_once 'layouts/main_layout.php';
?>
<?php
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

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
?> 
<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Get JSON data from request body
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Validate input
    if (!isset($data['assignment_id'])) {
        throw new Exception('Assignment ID is required');
    }

    $assignment_id = $data['assignment_id'];
    
    $conn = get_db_connection();

    // Verify assignment belongs to user
    $stmt = $conn->prepare("
        SELECT title FROM assignments 
        WHERE assignment_id = :assignment_id 
        AND user_id = :user_id
    ");
    
    $stmt->execute([
        'assignment_id' => $assignment_id,
        'user_id' => $_SESSION['user_id']
    ]);
    
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        throw new Exception('Assignment not found or access denied');
    }

    // Delete the assignment
    $stmt = $conn->prepare("
        DELETE FROM assignments 
        WHERE assignment_id = :assignment_id 
        AND user_id = :user_id
    ");

    $stmt->execute([
        'assignment_id' => $assignment_id,
        'user_id' => $_SESSION['user_id']
    ]);

    // Log the deletion
    log_message("Assignment deleted: {$assignment['title']}", 'INFO');

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Assignment deleted successfully'
    ]);

} catch (Exception $e) {
    // Log error
    log_message("Error deleting assignment: " . $e->getMessage(), 'ERROR');

    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
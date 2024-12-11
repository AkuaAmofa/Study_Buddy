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
    if (!isset($data['assignment_id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields');
    }

    $assignment_id = $data['assignment_id'];
    $new_status = $data['status'];

    // Validate status
    $allowed_statuses = ['Not Started', 'In Progress', 'Completed'];
    if (!in_array($new_status, $allowed_statuses)) {
        throw new Exception('Invalid status value');
    }

    $conn = get_db_connection();

    // Verify assignment belongs to user
    $stmt = $conn->prepare("
        SELECT user_id 
        FROM assignments 
        WHERE assignment_id = :assignment_id
    ");
    $stmt->execute(['assignment_id' => $assignment_id]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment || $assignment['user_id'] != $_SESSION['user_id']) {
        throw new Exception('Assignment not found or access denied');
    }

    // Update assignment status
    $stmt = $conn->prepare("
        UPDATE assignments 
        SET status = :status 
        WHERE assignment_id = :assignment_id 
        AND user_id = :user_id
    ");

    $stmt->execute([
        'status' => $new_status,
        'assignment_id' => $assignment_id,
        'user_id' => $_SESSION['user_id']
    ]);

    // Log the status update
    log_message("Assignment ID: $assignment_id status updated to: $new_status", 'INFO');

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);

} catch (Exception $e) {
    // Log error
    log_message("Error updating assignment status: " . $e->getMessage(), 'ERROR');

    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
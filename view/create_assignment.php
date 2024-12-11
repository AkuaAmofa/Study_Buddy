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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['title', 'course', 'due_date', 'priority'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("$field is required");
            }
        }

        // Sanitize and prepare data
        $title = trim($_POST['title']);
        $course = trim($_POST['course']);
        $due_date = trim($_POST['due_date']);
        $priority = trim($_POST['priority']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $user_id = $_SESSION['user_id'];

        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $due_date);
        if (!$date || $date->format('Y-m-d') !== $due_date) {
            throw new Exception('Invalid date format');
        }

        // Validate priority
        $allowed_priorities = ['Low', 'Medium', 'High'];
        if (!in_array($priority, $allowed_priorities)) {
            throw new Exception('Invalid priority level');
        }

        $conn = get_db_connection();

        // Insert new assignment
        $stmt = $conn->prepare("
            INSERT INTO assignments 
            (user_id, title, description, course, due_date, priority, status) 
            VALUES 
            (:user_id, :title, :description, :course, :due_date, :priority, 'Not Started')
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'title' => $title,
            'description' => $description,
            'course' => $course,
            'due_date' => $due_date,
            'priority' => $priority
        ]);

        // Log success
        log_message("New assignment created: $title by user ID: $user_id", 'INFO');

        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Assignment created successfully',
            'assignment_id' => $conn->lastInsertId()
        ]);

    } catch (Exception $e) {
        // Log error
        log_message("Error creating assignment: " . $e->getMessage(), 'ERROR');

        // Return error response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// If not POST request, redirect to assignments page
header('Location: assignments.php');
exit();
?> 
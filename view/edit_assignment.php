<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$assignment = null;
$error_message = '';

// Fetch assignment details for GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (!isset($_GET['id'])) {
            throw new Exception('Assignment ID not provided');
        }

        $conn = get_db_connection();
        $stmt = $conn->prepare("
            SELECT * FROM assignments 
            WHERE assignment_id = :id AND user_id = :user_id
        ");
        
        $stmt->execute([
            'id' => $_GET['id'],
            'user_id' => $_SESSION['user_id']
        ]);
        
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$assignment) {
            throw new Exception('Assignment not found or access denied');
        }
    } catch (Exception $e) {
        log_message("Error fetching assignment: " . $e->getMessage(), 'ERROR');
        $error_message = $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Validate input
        $required_fields = ['assignment_id', 'title', 'course', 'due_date', 'priority'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("$field is required");
            }
        }

        // Sanitize and prepare data
        $assignment_id = trim($_POST['assignment_id']);
        $title = trim($_POST['title']);
        $course = trim($_POST['course']);
        $due_date = trim($_POST['due_date']);
        $priority = trim($_POST['priority']);
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';

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

        // Verify assignment belongs to user
        $stmt = $conn->prepare("
            SELECT user_id FROM assignments 
            WHERE assignment_id = :assignment_id
        ");
        $stmt->execute(['assignment_id' => $assignment_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing || $existing['user_id'] != $_SESSION['user_id']) {
            throw new Exception('Assignment not found or access denied');
        }

        // Update assignment
        $stmt = $conn->prepare("
            UPDATE assignments 
            SET title = :title,
                description = :description,
                course = :course,
                due_date = :due_date,
                priority = :priority
            WHERE assignment_id = :assignment_id 
            AND user_id = :user_id
        ");

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'course' => $course,
            'due_date' => $due_date,
            'priority' => $priority,
            'assignment_id' => $assignment_id,
            'user_id' => $_SESSION['user_id']
        ]);

        log_message("Assignment updated: $title", 'INFO');
        echo json_encode(['success' => true, 'message' => 'Assignment updated successfully']);
        exit();

    } catch (Exception $e) {
        log_message("Error updating assignment: " . $e->getMessage(), 'ERROR');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment - Study Buddy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .buttons {
            margin-top: 20px;
        }
        .buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .save-btn {
            background-color: #007bff;
            color: white;
        }
        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Assignment</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($assignment): ?>
            <form id="edit-form">
                <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($assignment['assignment_id']); ?>">
                
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($assignment['course']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($assignment['due_date']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="priority">Priority:</label>
                    <select id="priority" name="priority">
                        <option value="Low" <?php echo $assignment['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $assignment['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $assignment['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($assignment['description']); ?></textarea>
                </div>

                <div class="buttons">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='assignments.php'">Cancel</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('edit-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch('edit_assignment.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Assignment updated successfully!');
                    window.location.href = 'assignments.php';
                } else {
                    alert('Error updating assignment: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating assignment');
            });
        });
    </script>
</body>
</html> 
<?php
//session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

$page_title = 'Assignments';
$current_page = 'assignments';

ob_start();
// Your existing assignments page content here
$content = ob_get_clean();

require_once 'layouts/main_layout.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    
    // Fetch existing assignments for the user
    $stmt = $conn->prepare("
        SELECT * FROM assignments 
        WHERE user_id = :user_id 
        ORDER BY 
            CASE 
                WHEN status = 'Not Started' THEN 1
                WHEN status = 'In Progress' THEN 2
                WHEN status = 'Completed' THEN 3
            END,
            CASE 
                WHEN priority = 'High' THEN 1
                WHEN priority = 'Medium' THEN 2
                WHEN priority = 'Low' THEN 3
            END,
            due_date ASC
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate completion statistics
    $total_assignments = count($assignments);
    $completed_assignments = count(array_filter($assignments, function($a) {
        return $a['status'] === 'Completed';
    }));
    $completion_rate = $total_assignments ? round(($completed_assignments / $total_assignments) * 100) : 0;

} catch (Exception $e) {
    log_message("Error fetching assignments: " . $e->getMessage(), 'ERROR');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Tracker - Study Buddy</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 40px;
            margin-left: 250px;
        }

        h1 {
            margin: 20px 0 30px 0;
            color: #333;
            padding-left: 20px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            width: calc(100% - 40px);
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.1rem;
        }

        .stat-card p {
            margin: 0;
            font-size: 1.8rem;
            color: #007bff;
            font-weight: 600;
        }

        .create-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .assignments-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .assignments-table th,
        .assignments-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .assignments-table th {
            background-color: #007bff;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .assignments-table tr:last-child td {
            border-bottom: none;
        }

        .assignments-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .priority-High { color: #dc3545; font-weight: 600; }
        .priority-Medium { color: #ffc107; font-weight: 600; }
        .priority-Low { color: #28a745; font-weight: 600; }

        .status-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 2px solid #e0e0e0;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .status-select:focus {
            border-color: #007bff;
            outline: none;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .edit-btn { 
            background-color: #ffc107;
            color: #000;
        }

        .delete-btn { 
            background-color: #dc3545;
            color: white;
        }

        .edit-btn:hover,
        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Submit button styling */
        .submit-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                margin-left: 220px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }

            .stats-container,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .assignments-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Assignment Tracker</h1>

        <!-- Statistics Section -->
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Assignments</h3>
                <p><?php echo $total_assignments; ?></p>
            </div>
            <div class="stat-card">
                <h3>Completed</h3>
                <p><?php echo $completed_assignments; ?></p>
            </div>
            <div class="stat-card">
                <h3>Completion Rate</h3>
                <p><?php echo $completion_rate; ?>%</p>
            </div>
        </div>

        <!-- Create Assignment Form -->
        <div class="create-form">
            <h2>Create New Assignment</h2>
            <form id="assignment-form" action="create_assignment.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="course">Course:</label>
                        <input type="text" id="course" name="course" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date:</label>
                        <input type="date" id="due_date" name="due_date" required>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority:</label>
                        <select id="priority" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                    Create Assignment
                </button>
            </form>
        </div>

        <!-- Assignments Table -->
        <table class="assignments-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Due Date</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                    <td><?php echo htmlspecialchars($assignment['course']); ?></td>
                    <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                    <td class="priority-<?php echo htmlspecialchars($assignment['priority']); ?>">
                        <?php echo htmlspecialchars($assignment['priority']); ?>
                    </td>
                    <td>
                        <select class="status-select" 
                                onchange="updateStatus(<?php echo $assignment['assignment_id']; ?>, this.value)">
                            <option value="Not Started" <?php echo $assignment['status'] == 'Not Started' ? 'selected' : ''; ?>>
                                Not Started
                            </option>
                            <option value="In Progress" <?php echo $assignment['status'] == 'In Progress' ? 'selected' : ''; ?>>
                                In Progress
                            </option>
                            <option value="Completed" <?php echo $assignment['status'] == 'Completed' ? 'selected' : ''; ?>>
                                Completed
                            </option>
                        </select>
                    </td>
                    <td class="action-buttons">
                        <button class="edit-btn" onclick="editAssignment(<?php echo $assignment['assignment_id']; ?>)">
                            Edit
                        </button>
                        <button class="delete-btn" onclick="deleteAssignment(<?php echo $assignment['assignment_id']; ?>)">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Add this to your assignments.php JavaScript section
document.getElementById('assignment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch('create_assignment.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Assignment created successfully!');
            location.reload();
        } else {
            alert('Error creating assignment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating assignment');
    });
});
        function updateStatus(assignmentId, newStatus) {
            fetch('update_assignment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    assignment_id: assignmentId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Optionally refresh the page or update the UI
                    location.reload();
                } else {
                    alert('Error updating status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        }

        function editAssignment(assignmentId) {
            // Redirect to edit page or show edit modal
            window.location.href = `edit_assignment.php?id=${assignmentId}`;
        }

        function deleteAssignment(assignmentId) {
            if (confirm('Are you sure you want to delete this assignment?')) {
                fetch('../actions/delete_assignment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        assignment_id: assignmentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting assignment: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting assignment');
                });
            }
        }
    </script>
</body>
</html> 
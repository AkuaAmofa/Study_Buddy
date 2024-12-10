<?php
require 'db.php'; // Database connection
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch existing assignments for the user
$stmt = $conn->prepare("SELECT * FROM assignments WHERE user_id = ? ORDER BY due_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Tracker - Study Buddy</title>
</head>
<body>
    <h1>Assignment Tracker</h1>

    <!-- Assignment Creation Form -->
    <section id="create-assignment">
        <h2>Create New Assignment</h2>
        <form id="assignment-form" action="create_assignment.php" method="POST">
            <div>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div>
                <label for="course">Course:</label>
                <input type="text" id="course" name="course" required>
            </div>

            <div>
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>

            <div>
                <label for="priority">Priority:</label>
                <select id="priority" name="priority">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
            </div>

            <button type="submit">Create Assignment</button>
        </form>
    </section>

    <!-- Assignment List -->
    <section id="assignment-list">
        <h2>Your Assignments</h2>
        <table>
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
                <tr data-id="<?php echo $assignment['assignment_id']; ?>">
                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                    <td><?php echo htmlspecialchars($assignment['course']); ?></td>
                    <td><?php echo $assignment['due_date']; ?></td>
                    <td><?php echo $assignment['priority']; ?></td>
                    <td>
                        <select class="status-select" onchange="updateStatus(this)">
                            <option value="Not Started" <?php echo $assignment['status'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                            <option value="In Progress" <?php echo $assignment['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo $assignment['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </td>
                    <td>
                        <button onclick="editAssignment(<?php echo $assignment['assignment_id']; ?>)">Edit</button>
                        <button onclick="deleteAssignment(<?php echo $assignment['assignment_id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <script src="js/assignments.js"></script>
</body>
</html> 
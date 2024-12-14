<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

// Initialize variables
$page_title = 'Assignments';
$current_page = 'assignments';
$error_message = '';
$success_message = '';
$assignments = [];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = get_db_connection();
    
    // Fetch user's assignments
    $stmt = $conn->prepare("
        SELECT * FROM assignments 
        WHERE user_id = ? 
        ORDER BY due_date ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    log_message("Error in assignments page: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading assignments.";
}

ob_start();
?>

<style>
.dashboard {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    color: #333;
    font-size: 2rem;
}

.add-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #2196F3;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.3s ease;
}

.add-button:hover {
    background: #1976D2;
}

.assignments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.assignment-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.assignment-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.assignment-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: start;
}

.assignment-header h3 {
    color: #333;
    margin: 0;
    font-size: 1.25rem;
}

.status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status.pending {
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

.assignment-body {
    padding: 1.5rem;
}

.assignment-body p {
    color: #666;
    margin: 0 0 1rem 0;
    line-height: 1.5;
}

.course, .due-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.875rem;
}

.course i, .due-date i {
    color: #2196F3;
}

.assignment-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.assignment-footer button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.assignment-footer button:first-child {
    background: #E3F2FD;
    color: #1976D2;
}

.assignment-footer button:last-child {
    background: #FFEBEE;
    color: #D32F2F;
}

.assignment-footer button:hover {
    opacity: 0.9;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-state p {
    color: #666;
    margin: 0.5rem 0;
}

.empty-state p:first-child {
    font-size: 1.25rem;
    color: #333;
}

.error-message {
    background: #FFEBEE;
    color: #D32F2F;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .dashboard {
        padding: 1rem;
    }

    .assignments-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}

/* Add New Assignment Form Styles */
.add-assignment-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.add-assignment-section h2 {
    color: #333;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.assignment-form {
    display: grid;
    gap: 1rem;
    max-width: 600px;
}

.form-row {
    display: flex;
    gap: 1rem;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #666;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="date"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.add-btn, .cancel-btn {
    background: white;
    color: #2196F3;
    border: 2px solid #2196F3;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.add-btn:hover, .cancel-btn:hover {
    background: #E3F2FD;
}

/* Update the add button in header */
.add-button {
    background: #2196F3;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    font-size: 1rem;
}

.add-button:hover {
    background: #1976D2;
}

/* Status styles */
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

/* Priority styles */
.priority {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.priority.low {
    color: #388E3C;
}

.priority.medium {
    color: #F57C00;
}

.priority.high {
    color: #D32F2F;
}

/* Update button styles */
.btn-outline {
    background: white;
    color: #2196F3;
    border: 2px solid #2196F3;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background: #E3F2FD;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

/* Make sure input[type="submit"] has the same styling */
input[type="submit"].btn-outline {
    background: white;
    color: #2196F3;
    border: 2px solid #2196F3;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
}

input[type="submit"].btn-outline:hover {
    background: #E3F2FD;
}
</style>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Assignments</h1>
        <button onclick="showAddAssignmentModal()" class="add-button">
            <i class='bx bx-plus'></i> Add Assignment
        </button>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="assignments-grid">
        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <p>No assignments yet.</p>
                <p>Click the "Add Assignment" button to get started!</p>
            </div>
        <?php else: ?>
            <?php foreach ($assignments as $assignment): ?>
                <div class="assignment-card">
                    <div class="assignment-header">
                        <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                        <span class="status <?php echo strtolower($assignment['status']); ?>">
                            <?php echo htmlspecialchars($assignment['status']); ?>
                        </span>
                    </div>
                    <div class="assignment-body">
                        <p><?php echo htmlspecialchars($assignment['description'] ?? ''); ?></p>
                        <p class="course">
                            <i class='bx bx-book'></i>
                            <?php echo htmlspecialchars($assignment['course']); ?>
                        </p>
                        <p class="due-date">
                            <i class='bx bx-calendar'></i>
                            Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                        </p>
                        <p class="priority <?php echo strtolower($assignment['priority']); ?>">
                            <i class='bx bx-flag'></i>
                            Priority: <?php echo htmlspecialchars($assignment['priority']); ?>
                        </p>
                    </div>
                    <div class="assignment-footer">
                        <button onclick="window.location.href='edit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>'" class="edit-btn">
                            <i class='bx bx-edit'></i> Edit
                        </button>
                        <button onclick="deleteAssignment(<?php echo $assignment['assignment_id']; ?>)" class="delete-btn">
                            <i class='bx bx-trash'></i> Delete
                        </button>
                        <select onchange="updateStatus(<?php echo $assignment['assignment_id']; ?>, this.value)" class="status-select">
                            <option value="Not Started" <?php echo $assignment['status'] === 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                            <option value="In Progress" <?php echo $assignment['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo $assignment['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Assignment Modal -->
<div id="addAssignmentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Add New Assignment</h2>
        <form id="addAssignmentForm">
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
                <select id="priority" name="priority" required>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="submit-btn">Add Assignment</button>
                <button type="button" onclick="closeModal()" class="cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddAssignmentModal() {
    document.getElementById('addAssignmentModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addAssignmentModal').style.display = 'none';
}

document.getElementById('addAssignmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('create_assignment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the assignment');
    });
});

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
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the assignment');
        });
    }
}

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
            location.reload();
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
    });
}
</script>

<?php
$content = ob_get_clean();
require_once 'layouts/main_layout.php';
?> 
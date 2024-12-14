<?php
session_start();
require_once '../db/db.php';
require_once '../db/logger.php';

$page_title = 'Add Assignment';
$current_page = 'assignments';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

ob_start();
?>

<div class="form-container">
    <div class="form-header">
        <h1><i class='bx bx-plus-circle'></i> Add New Assignment</h1>
        <p>Create a new assignment to track your academic tasks</p>
    </div>

    <form action="create_assignment.php" method="POST" class="assignment-form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required 
                   placeholder="Enter assignment title">
        </div>

        <div class="form-group">
            <label for="course">Course</label>
            <input type="text" id="course" name="course" required 
                   placeholder="Enter course name">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" 
                      placeholder="Enter assignment details"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="submit-btn">
                <i class='bx bx-save'></i> Save Assignment
            </button>
            <a href="assignments.php" class="cancel-btn">
                <i class='bx bx-x'></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
.form-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-header h1 {
    color: #2196F3;
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.form-header p {
    color: #666;
}

.assignment-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group label {
    color: #333;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #2196F3;
    outline: none;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1rem;
}

.submit-btn,
.cancel-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.submit-btn {
    background: #2196F3;
    color: white;
}

.submit-btn:hover {
    background: #1976D2;
}

.cancel-btn {
    background: #f44336;
    color: white;
}

.cancel-btn:hover {
    background: #d32f2f;
}

@media (max-width: 768px) {
    .form-container {
        margin: 1rem;
        padding: 1.5rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once 'layouts/main_layout.php';
?> 
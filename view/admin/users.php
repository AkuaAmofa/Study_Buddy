<?php
session_start();
require_once '../../db/db.php';
require_once '../../db/logger.php';

$page_title = 'User Management';
$current_page = 'users';

// Initialize variables
$users = [];
$error_message = '';
$success_message = '';
$search_term = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$total_pages = 1;

try {
    $conn = get_db_connection();
    
    // Simplified query first to make sure we can get users
    $query = "
        SELECT 
            user_id,
            username,
            email,
            first_name,
            last_name,
            created_at,
            is_admin,
            COALESCE(is_active, 1) as is_active
        FROM users
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $offset = ($page - 1) * $per_page;
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug information
    if (empty($users)) {
        log_message("No users found in database", 'DEBUG');
    }
    
    // Get total users count for pagination
    $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Calculate total pages
    $total_pages = ceil($total_users / $per_page);
    
} catch (Exception $e) {
    // More detailed error logging
    log_message("Error in admin users page: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString(), 'ERROR');
    $error_message = "An error occurred while loading the users: " . $e->getMessage();
}

ob_start();
?>

<style>
    .users-container {
        padding: 20px;
    }

    .search-bar {
        width: 100%;
        max-width: 500px;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .users-table th,
    .users-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .users-table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.85em;
    }

    .status-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-inactive {
        background: #ffebee;
        color: #c62828;
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 5px;
        font-size: 0.9em;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }

    .btn-delete:hover {
        background: #c82333;
    }

    .admin-badge {
        background: #6c757d;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .page-link {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
    }

    .page-link.active {
        background: #2196F3;
        color: white;
        border-color: #2196F3;
    }
</style>

<div class="users-container">
    <h1>User Management</h1>
    
    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$user['is_admin']): ?>
                            <button class="action-btn btn-delete" 
                                    onclick="deleteUser(<?php echo $user['user_id']; ?>)">
                                Delete User
                            </button>
                        <?php else: ?>
                            <span class="admin-badge">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-results">
            <p>No users found matching your search criteria.</p>
        </div>
    <?php endif; ?>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>" 
                   class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../../js/admin_users.js" defer></script>

<?php
$content = ob_get_clean();
require_once 'admin_layout.php';
?> 
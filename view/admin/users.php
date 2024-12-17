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

try {
    $conn = get_db_connection();
    
    // Build search query
    $query = "
        SELECT 
            user_id,
            username,
            email,
            first_name,
            last_name,
            created_at,
            is_active
        FROM users
        WHERE (username LIKE :search 
            OR email LIKE :search 
            OR first_name LIKE :search 
            OR last_name LIKE :search)
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $search_param = "%{$search_term}%";
    $offset = ($page - 1) * $per_page;
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total users count for pagination
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username LIKE :search OR email LIKE :search");
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
    $stmt->execute();
    $total_users = $stmt->fetchColumn();
    
    $total_pages = ceil($total_users / $per_page);
    
} catch (Exception $e) {
    log_message("Error in admin users page: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading the users.";
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

    .btn-toggle {
        background: #2196F3;
        color: white;
    }

    .btn-reset {
        background: #ff9800;
        color: white;
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
    
    <input type="text" 
           class="search-bar" 
           placeholder="Search users by name or email..." 
           value="<?php echo htmlspecialchars($search_term); ?>"
           onkeyup="handleSearch(this.value)">
    
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

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
                    <button class="action-btn btn-toggle" 
                            onclick="toggleUserStatus(<?php echo $user['user_id']; ?>)">
                        <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                    </button>
                    <button class="action-btn btn-reset" 
                            onclick="resetPassword(<?php echo $user['user_id']; ?>)">
                        Reset Password
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>" 
               class="page-link <?php echo $page === $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

<script src="js/admin_users.js" defer></script>

<?php
$content = ob_get_clean();
require_once 'admin_layout.php';
?> 
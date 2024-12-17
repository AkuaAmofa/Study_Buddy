<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - Study Buddy Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --sidebar-width: 240px;
            --navbar-height: 60px;
            --primary-color: #2196F3;
            --secondary-color: #1976D2;
            --text-color: #333;
            --bg-color: #f5f5f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #1a237e;
            padding: 1rem;
            color: white;
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            padding: 0.5rem;
            color: white;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: background 0.3s;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-item.active {
            background: var(--primary-color);
        }

        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">Admin Panel</div>
        <nav>
            <a href="dashboard.php" class="menu-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <i class='bx bx-home'></i> Dashboard
            </a>
            <a href="users.php" class="menu-item <?php echo $current_page === 'users' ? 'active' : ''; ?>">
                <i class='bx bx-user'></i> Users
            </a>
            <a href="../index.php" class="menu-item">
                <i class='bx bx-arrow-back'></i> Back to Site
            </a>
            <a href="../logout.php" class="menu-item">
                <i class='bx bx-log-out'></i> Logout
            </a>
        </nav>
    </div>

    <main class="main-content">
        <?php echo $content ?? ''; ?>
    </main>
</body>
</html> 
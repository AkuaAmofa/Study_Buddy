<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Study Buddy'; ?></title>
    <!-- Box Icons CSS -->
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

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: white;
            padding: 1rem;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 2rem;
            padding: 0.5rem;
        }

        .sidebar-menu {
            flex: 1;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: background 0.3s;
        }

        .menu-item:hover {
            background: rgba(33, 150, 243, 0.1);
        }

        .menu-item.active {
            background: var(--primary-color);
            color: white;
        }

        .menu-item i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .logout-item {
            margin-top: auto;
            color: #f44336;
        }

        /* Navbar Styles */
        .navbar {
            position: fixed;
            left: var(--sidebar-width);
            right: 0;
            top: 0;
            height: var(--navbar-height);
            background: white;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .nav-btn {
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 4px;
            color: var(--text-color);
        }

        .nav-btn:hover {
            background: rgba(0,0,0,0.05);
        }

        .search-container {
            flex: 1;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-bar {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-btn {
            position: relative;
            padding: 0.5rem;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #f44336;
            color: white;
            border-radius: 50%;
            padding: 0.25rem;
            font-size: 0.75rem;
            min-width: 18px;
            height: 18px;
            text-align: center;
        }

        .profile-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
        }

        .profile-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Main Content Area */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 2rem;
            max-width: 1200px;
            margin-right: auto;
            margin-left: calc(var(--sidebar-width) + auto);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">Study Buddy</div>
        <nav class="sidebar-menu">
            <a href="home.php" class="menu-item <?php echo $current_page === 'home' ? 'active' : ''; ?>">
                <i class='bx bx-home'></i> Home
            </a>
            <a href="assignments.php" class="menu-item <?php echo $current_page === 'assignments' ? 'active' : ''; ?>">
                <i class='bx bx-task'></i> Assignments
            </a>
            <a href="study-buddies.php" class="menu-item <?php echo $current_page === 'study-buddies' ? 'active' : ''; ?>">
                <i class='bx bx-group'></i> Study Buddies
            </a>
            <a href="resources.php" class="menu-item <?php echo $current_page === 'resources' ? 'active' : ''; ?>">
                <i class='bx bx-book'></i> Resources
            </a>
            <a href="logout.php" class="menu-item logout-item">
                <i class='bx bx-log-out'></i> Logout
            </a>
        </nav>
    </div>

    <!-- Navbar -->
    <div class="navbar">
        <div class="nav-buttons">
            <button class="nav-btn" onclick="history.back()">
                <i class='bx bx-arrow-back'></i>
            </button>
            <button class="nav-btn" onclick="history.forward()">
                <i class='bx bx-arrow-forward'></i>
            </button>
        </div>

        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Search resources, users, assignments...">
        </div>

        <div class="nav-actions">
            <div class="notification-btn">
                <i class='bx bx-bell'></i>
                <span class="notification-badge">3</span>
            </div>
            <a href="profile.php" class="profile-btn">
                <img src="../assets/images/default-avatar.png" alt="Profile">
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <?php echo $content ?? ''; ?>
    </main>

    <script>
        // Handle notifications dropdown
        document.querySelector('.notification-btn').addEventListener('click', () => {
            window.location.href = 'notifications.php';
        });

        // Global search functionality
        const searchBar = document.querySelector('.search-bar');
        searchBar.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const query = searchBar.value.trim();
                if (query) {
                    window.location.href = `search.php?q=${encodeURIComponent(query)}`;
                }
            }
        });
    </script>
</body>
</html> 
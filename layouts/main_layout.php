<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - StudyBuddy</title>
    <!-- Add your CSS files -->
    <link rel="stylesheet" href="../css/home.css">
    <?php if (isset($current_page) && $current_page === 'study-buddies'): ?>
        <link rel="stylesheet" href="../css/study_buddies.css">
    <?php endif; ?>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="home.php">📚 Study Buddy</a>
            </div>
            <div class="nav-links">
                <a href="home.php">🏠 Home</a>
                <a href="assignments.php">📝 Assignments</a>
                <a href="study-buddies.php">👥 Study Network</a>
                <a href="resources.php">📚 Resources</a>
                <div class="profile-menu">
                    <a href="#" class="profile-trigger">👤 Profile</a>
                    <div class="dropdown-menu">
                        <a href="profile.php">
                            <i class="bx bx-user-circle"></i> My Profile
                        </a>
                        <a href="logout.php" class="logout">
                            <i class="bx bx-log-out"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main content -->
    <?php echo $content; ?>

</body>
</html> 
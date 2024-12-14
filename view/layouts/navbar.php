<nav class="navbar">
    <div class="nav-brand">
        <a href="home.php">
            <i class='bx bx-book-reader'></i>
            <span>Study Buddy</span>
        </a>
    </div>
    
    <div class="nav-links">
        <a href="home.php" class="<?php echo $current_page === 'home' ? 'active' : ''; ?>">
            <i class='bx bx-home'></i>
            <span>Home</span>
        </a>
        
        <a href="assignments.php" class="<?php echo $current_page === 'assignments' ? 'active' : ''; ?>">
            <i class='bx bx-task'></i>
            <span>Assignments</span>
        </a>
        
        <a href="study-buddies.php" class="<?php echo $current_page === 'study-buddies' ? 'active' : ''; ?>">
            <i class='bx bx-group'></i>
            <span>Study Network</span>
        </a>
        
        <a href="resources.php" class="<?php echo $current_page === 'resources' ? 'active' : ''; ?>">
            <i class='bx bx-library'></i>
            <span>Resources</span>
        </a>
    </div>
    
    <div class="nav-profile">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class='bx bx-user-circle'></i>
                    <span>Profile</span>
                </button>
                <div class="dropdown-menu">
                    <a href="profile.php">
                        <i class='bx bx-user'></i>
                        <span>My Profile</span>
                    </a>
                    <a href="settings.php">
                        <i class='bx bx-cog'></i>
                        <span>Settings</span>
                    </a>
                    <a href="logout.php" class="logout">
                        <i class='bx bx-log-out'></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="login-btn">
                <i class='bx bx-log-in'></i>
                <span>Login</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-brand a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.5rem;
    font-weight: bold;
    color: #2196F3;
    text-decoration: none;
}

.nav-links {
    display: flex;
    gap: 2rem;
}

.nav-links a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-links a:hover,
.nav-links a.active {
    color: #2196F3;
    background: #E3F2FD;
}

.nav-profile {
    position: relative;
}

.dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    background: none;
    cursor: pointer;
    color: #666;
}

.dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 0.5rem;
    display: none;
}

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: #666;
    text-decoration: none;
    white-space: nowrap;
}

.dropdown-menu a:hover {
    background: #E3F2FD;
    border-radius: 4px;
}

.logout {
    color: #f44336 !important;
}

.login-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #2196F3;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.3s ease;
}

.login-btn:hover {
    background: #1976D2;
}
</style> 
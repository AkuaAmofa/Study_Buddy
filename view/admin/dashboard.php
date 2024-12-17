<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../db/db.php';
require_once '../../db/logger.php';

$page_title = 'Admin Dashboard';
$current_page = 'dashboard';

// Initialize variables
$total_users = 0;
$active_connections = 0;
$total_resources = 0;
$monthly_signups = [];

try {
    $conn = get_db_connection();
    
    // Get total users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
    
    // Get active study buddy connections
    $stmt = $conn->query("SELECT COUNT(*) FROM studybuddyconnections WHERE status = 'Accepted'");
    $active_connections = $stmt->fetchColumn();
    
    // Get total resources
    $stmt = $conn->query("SELECT COUNT(*) FROM resources");
    $total_resources = $stmt->fetchColumn();
    
    // Get signups for current month
    $stmt = $conn->query("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM users 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $monthly_signups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    log_message("Error in admin dashboard: " . $e->getMessage(), 'ERROR');
    $error_message = "An error occurred while loading the dashboard.";
}

ob_start();
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .stat-card h3 {
        margin: 0 0 10px 0;
        color: #666;
    }

    .stat-card .number {
        font-size: 2em;
        font-weight: bold;
        color: var(--primary-color);
    }

    .chart-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<h1>Admin Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="number"><?php echo number_format($total_users); ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Active Study Buddy Connections</h3>
        <div class="number"><?php echo number_format($active_connections); ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Total Shared Resources</h3>
        <div class="number"><?php echo number_format($total_resources); ?></div>
    </div>
</div>

<div class="chart-container">
    <h2>New Signups This Month</h2>
    <canvas id="signupsChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const signupsData = <?php echo json_encode($monthly_signups); ?>;
    
    new Chart(document.getElementById('signupsChart'), {
        type: 'line',
        data: {
            labels: signupsData.map(item => item.date),
            datasets: [{
                label: 'New Signups',
                data: signupsData.map(item => item.count),
                borderColor: '#2196F3',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

<?php
$content = ob_get_clean();
require_once 'admin_layout.php';
?> 
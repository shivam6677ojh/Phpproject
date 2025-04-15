<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get total counts
$careers_count = $db->query("SELECT COUNT(*) FROM careers")->fetchColumn();
$messages_count = $db->query("SELECT COUNT(*) FROM messages WHERE status = 'new'")->fetchColumn();

// Get recent careers
$recent_careers = $db->query("SELECT * FROM careers ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get recent messages
$recent_messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Career Explorer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --background-dark: #1a1a1a;
            --card-dark: #2d2d2d;
            --text-light: #ffffff;
            --text-muted: #b0b0b0;
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-dark);
            color: var(--text-light);
            line-height: 1.6;
        }

        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--card-dark);
            padding: 2rem;
            position: fixed;
            width: 250px;
            height: 100vh;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header {
            margin-bottom: 2rem;
        }

        .sidebar-header h1 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-light);
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(74, 144, 226, 0.2);
            color: var(--primary-color);
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            padding: 2rem;
            margin-left: 250px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-header h2 {
            color: var(--text-light);
            font-size: 1.8rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu a {
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
        }

        .user-menu a:hover {
            color: var(--primary-color);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-dark);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Recent Items */
        .recent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .recent-card {
            background: var(--card-dark);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .recent-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .recent-card-header h3 {
            color: var(--text-light);
            font-size: 1.2rem;
        }

        .recent-card-header a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .recent-list {
            list-style: none;
        }

        .recent-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .recent-item-title {
            color: var(--text-light);
            font-weight: 500;
        }

        .recent-item-date {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .recent-item-meta {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .message-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-new {
            background: rgba(74, 144, 226, 0.2);
            color: var(--primary-color);
        }

        .status-read {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }

        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>Career Explorer</h1>
                <p>Admin Dashboard</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2>Dashboard Overview</h2>
                <div class="user-menu">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Careers</h3>
                    <div class="number"><?php echo $careers_count; ?></div>
                </div>
                <div class="stat-card">
                    <h3>New Messages</h3>
                    <div class="number"><?php echo $messages_count; ?></div>
                </div>
            </div>

            <div class="recent-grid">
                <div class="recent-card">
                    <div class="recent-card-header">
                        <h3>Recent Careers</h3>
                        <a href="careers.php">View All</a>
                    </div>
                    <ul class="recent-list">
                        <?php foreach ($recent_careers as $career): ?>
                            <li class="recent-item">
                                <div class="recent-item-header">
                                    <span class="recent-item-title"><?php echo htmlspecialchars($career['title']); ?></span>
                                    <span class="recent-item-date"><?php echo date('M d, Y', strtotime($career['created_at'])); ?></span>
                                </div>
                                <div class="recent-item-meta">
                                    <?php echo htmlspecialchars($career['industry']); ?> • 
                                    <?php echo htmlspecialchars($career['education_level']); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="recent-card">
                    <div class="recent-card-header">
                        <h3>Recent Messages</h3>
                        <a href="messages.php">View All</a>
                    </div>
                    <ul class="recent-list">
                        <?php foreach ($recent_messages as $message): ?>
                            <li class="recent-item">
                                <div class="recent-item-header">
                                    <span class="recent-item-title"><?php echo htmlspecialchars($message['subject']); ?></span>
                                    <span class="message-status status-<?php echo $message['status']; ?>">
                                        <?php echo ucfirst($message['status']); ?>
                                    </span>
                                </div>
                                <div class="recent-item-meta">
                                    <?php echo htmlspecialchars($message['name']); ?> • 
                                    <?php echo htmlspecialchars($message['email']); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 
<?php
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$currentUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($currentUrl);
$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
?>

<aside class="dashboard-sidebar">
    <div class="dashboard-brand">
        <div class="dashboard-brand-badge">JT</div>
        <div>
            <h1>Jira Task Tool</h1>
            <p>Team productivity dashboard</p>
        </div>
    </div>

    <div class="dashboard-nav-group">
        <div class="dashboard-nav-title">Overview</div>
        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a class="dashboard-nav-item <?= $path === '/dashboard' ? 'active' : '' ?>" href="/dashboard">
            <span class="dashboard-nav-dot"></span>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>
        <a class="dashboard-nav-item <?= $path === '/board' ? 'active' : '' ?>" href="/board">
            <span class="dashboard-nav-dot"></span>
            <span>Tasks</span>
        </a>
    </div>

    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <div class="dashboard-nav-group">
            <div class="dashboard-nav-title">Users</div>
            <a class="dashboard-nav-item <?= $path === '/users' ? 'active' : '' ?>" href="/users">
                <span class="dashboard-nav-dot"></span>
                <span>All Users</span>
            </a>
            <a class="dashboard-nav-item <?= $path === '/user-new' ? 'active' : '' ?>" href="/user-new">
                <span class="dashboard-nav-dot"></span>
                <span>Add New User</span>
            </a>
        </div>
    <?php endif; ?>

    <div class="dashboard-nav-group">
        <div class="dashboard-nav-title">Manage</div>
        <a class="dashboard-nav-item <?= $path === '/create-task' ? 'active' : '' ?>" href="/create-task">
            <span class="dashboard-nav-dot"></span>
            <span>Create Task</span>
        </a>
        <a class="dashboard-nav-item" href="/logout">
            <span class="dashboard-nav-dot"></span>
            <span>Sign out</span>
        </a>
    </div>

    <div class="dashboard-sidebar-card">
        <h3>Weekly health</h3>
        <p>Completion rate is strong this week. A few overdue items still need review from the editor team.</p>
        <?php if ($path === '/dashboard'): ?>
            <button type="button" id="sidebarRefresh">Refresh dashboard</button>
        <?php elseif ($path === '/users'): ?>
            <a href="/users" class="dashboard-sidebar-link">Review users</a>
        <?php elseif ($path === '/create-task'): ?>
            <a href="/create-task" class="dashboard-sidebar-link">Open task form</a>
        <?php elseif ($path === '/board'): ?>
            <a href="/board" class="dashboard-sidebar-link">Open Tasks</a>
        <?php else: ?>
            <a href="/dashboard" class="dashboard-sidebar-link">Go to dashboard</a>
        <?php endif; ?>
    </div>
</aside>

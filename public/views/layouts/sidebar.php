<?php
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$currentUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($currentUrl);
$path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
?>

<div class="menu-mobile">
    <div class="dashboard-brand">
        <div class="dashboard-brand-badge">JT</div>
        <div>
            <h1>Jira Task Tool</h1>
            <p>Team productivity dashboard</p>
        </div>
    </div>
    <div class="btn-menu" id="btnMenu">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

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
        <a class="dashboard-nav-item <?= $path === '/attachments' ? 'active' : '' ?>" href="/attachments">
            <span class="dashboard-nav-dot"></span>
            <span>Attachments</span>
        </a>
        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a class="dashboard-nav-item <?= $path === '/archived-work-items' ? 'active' : '' ?>" href="/archived-work-items">
            <span class="dashboard-nav-dot"></span>
            <span>Archived Work Items</span>
        </a>
        <?php endif; ?>
    </div>

    <div class="dashboard-sidebar-card">
        <div class="user-info">
            <div class="avt-user">
                <img src="../../assets/images/avt.png" width="60" alt="">
            </div>
            <div class="user-name">
                <p class="name"><?php echo $_SESSION["user"]["username"]; ?></p>
                <p class="role"><?php echo $_SESSION["user"]["role"]; ?></p>
            </div>
        </div>
        <div class="sign-out">
            <a class="" href="/logout">
                <span>Sign out</span>
            </a>
        </div>
    </div>
</aside>

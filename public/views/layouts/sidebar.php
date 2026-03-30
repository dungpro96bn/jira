<?php
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$currentUrl = $scheme . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$url = $currentUrl;
$parsedUrl = parse_url($url);
$path = $parsedUrl['path'];
?>

<aside id="sidebar">
    <div class="sidebar-container">
        <div class="sidebar-title">
            <p>Dashboard</p>
        </div>
        <ul class="link-list">
            <li class="link-item">
                <a href="/users" class="<?= $path == '/users' ? 'active' : '' ?>">All Users</a>
            </li>
            <li class="link-item">
                <a href="/user-new" class="<?= $path == '/user-new' ? 'active' : '' ?>">Add New User</a>
            </li>
            <li class="link-item">
                <a href="/summary" class="<?= $path == '/summary' ? 'active' : '' ?>">Summary</a>
            </li>

        </ul>
    </div>
</aside>
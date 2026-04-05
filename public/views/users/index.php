<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="app dashboard-app-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main">
        <section class="dashboard-topbar">
            <div class="dashboard-heading-block">
                <h2>All Users</h2>
            </div>
            <div class="dashboard-topbar-actions">
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/dashboard" class="dashboard-button">Back to Dashboard</a>
                <?php endif; ?>
                <a href="/user-new" class="dashboard-button dashboard-button-primary"><i class="fa-solid fa-plus"></i>Add New User</a>
            </div>
        </section>

        <section class="panel user-dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">User Management</h3>
                    <p class="panel-subtitle">Update user information, switch roles instantly, reset passwords, or remove accounts.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="users-table-wrap">
                    <table class="users-dashboard-table">
                        <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Password</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr class="user-row" data-id="<?= $u['id'] ?>">
                                <td>
                                    <input type="text" class="input-username dashboard-input" value="<?= htmlspecialchars($u['username']) ?>" disabled>
                                </td>
                                <td>
                                    <input type="email" class="input-email dashboard-input" title="<?= htmlspecialchars($u['email']) ?>" value="<?= htmlspecialchars($u['email']) ?>" disabled>
                                </td>
                                <td>
                                    <select class="role-select dashboard-select">
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="password" class="password-input dashboard-input" value="********" disabled>
                                </td>
                                <td>
                                    <div class="user-actions-row">
                                        <div class="user-action-group">
                                            <button class="user-action-btn edit-user" title="Edit"><i class="fa-regular fa-pen-to-square"></i><span>Edit</span></button>
                                            <button class="user-action-btn save-user save-btn" style="display:none;">Save</button>
                                            <button class="user-action-btn cancel-user cancel-btn" style="display:none;">Cancel</button>
                                        </div>
                                        <div class="user-action-group">
                                            <button class="user-action-btn delete" title="Delete"><i class="fa-solid fa-xmark"></i><span>Delete</span></button>
                                        </div>
                                        <div class="user-action-group">
                                            <button class="user-action-btn change-pass" title="Change password"><i class="fa-solid fa-key"></i><span>Password</span></button>
                                            <button class="user-action-btn save-pass save-btn" style="display:none;">Save</button>
                                            <button class="user-action-btn cancel-pass cancel-btn" style="display:none;">Cancel</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

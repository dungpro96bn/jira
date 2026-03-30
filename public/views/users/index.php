<?php require __DIR__ . '/../layouts/header.php'; ?>

    <main id="user-page">
        <div class="inner">

            <div class="container-panel">

                <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

                <div class="column-main">
                    <div class="user-container">
                        <div class="top-title">
                            <h2>Users</h2>
                        </div>

                        <div class="user-table">

                            <div class="user-row user-header">
                                <div class="col username">Username</div>
                                <div class="col email">Email</div>
                                <div class="col role">Role</div>
                                <div class="col password">Password</div>
                                <div class="col actions">Actions</div>
                            </div>

                            <div class="inner-scroll">
                                <?php foreach ($users as $u): ?>
                                    <div class="user-row" data-id="<?= $u['id'] ?>">
                                        <div class="col username">
                                            <input type="text" class="input-username" value="<?= $u['username'] ?>" disabled>
                                        </div>

                                        <div class="col email">
                                            <input type="email" class="input-email" title="<?= $u['email'] ?>" value="<?= $u['email'] ?>" disabled>
                                        </div>

                                        <div class="col role">
                                            <select class="role-select">
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            </select>
                                        </div>

                                        <div class="col password">
                                            <input type="password" class="password-input" value="********" disabled>
                                        </div>

                                        <div class="col actions">
                                            <div class="action-item">
                                                <button class="edit-user" title="Edit"><i class="fa-regular fa-pen-to-square"></i></button>
                                                <button class="save-user save-btn" style="display:none;">Save</button>
                                                <button class="cancel-user cancel-btn" style="display:none;">Cancel</button>
                                            </div>
                                            <div class="action-item">
                                                <button title="Delete" class="delete"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div class="action-item">
                                                <button class="change-pass" title="Change password"><i class="fa-solid fa-key"></i></button>
                                                <button class="save-pass save-btn" style="display:none;">Save</button>
                                                <button class="cancel-pass cancel-btn" style="display:none;">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
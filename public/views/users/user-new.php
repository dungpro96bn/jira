<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="app dashboard-app-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main">
        <section class="dashboard-topbar">
            <div class="dashboard-heading-block">
                <h2>Add New User</h2>
            </div>
            <div class="dashboard-topbar-actions">
                <a href="/users" class="dashboard-button">All Users</a>
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/dashboard" class="dashboard-button dashboard-button-primary">Dashboard</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel user-form-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Create User</h3>
                    <p class="panel-subtitle">Fill in the account information below.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="dashboard-form-wrap">
                    <form autocomplete="off" method="POST" action="/user-new" id="formCreateUser" class="dashboard-form-grid">
                        <div class="group-field">
                            <label for="username">Username</label>
                            <div class="input-field">
                                <input class="dashboard-input" type="text" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="group-field">
                            <label for="email">Email</label>
                            <div class="input-field">
                                <input class="dashboard-input" type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="group-field">
                            <label for="role">Role</label>
                            <div class="input-field">
                                <select name="role" id="role" class="role-select-new dashboard-select">
                                    <option value="">Select role</option>
                                    <option value="admin">Admin</option>
                                    <option value="editor">Editor</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                        <div class="group-field">
                            <label for="password-field">Password</label>
                            <div class="input-field">
                                <div class="container-input dashboard-password-row">
                                    <input class="dashboard-input" type="password" id="password-field" name="password" required>
                                    <span id="generateBtn" class="dashboard-inline-btn">Generate</span>
                                </div>
                            </div>
                        </div>
                        <div class="group-field group-field-full">
                            <label for="referral_code">Referral code</label>
                            <div class="input-field">
                                <input class="dashboard-input" type="text" id="referral_code" name="referral_code" required>
                            </div>
                        </div>
                        <div class="submit-form group-field-full">
                            <input class="dashboard-button dashboard-button-primary dashboard-submit" type="submit" value="Register">
                        </div>
                    </form>

                    <?php if (isset($success)): ?>
                        <p class="dashboard-note success"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <p class="dashboard-note error"><?php echo $error; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
    $(function () {
        setTimeout(function () {
            $('#formCreateUser input[type="text"]').val('');
            $('#formCreateUser input[type="password"]').val('');
            $('#formCreateUser input[type="email"]').val('');
        }, 1000)
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main id="login">
    <div class="login-main">
        <h2>Login</h2>
        <form method="POST" action="/login">
            <div class="group-field">
                <label for="username">Username</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="group-field field-pass">
                <label for="password">Password</label>
                <div class="container-input">
                    <input id="password-field" type="password" name="password" required>
                    <span toggle="#password-field" class="fa-regular fa-eye-slash field-icon toggle-password"></span>
                </div>
            </div>
            <div class="remember-me">
                <label>
                    <input type="checkbox"value="yes" name="remember"> Remember me
                </label>
            </div>
            <div class="submit-form">
                <input class="login-btn" type="submit" name="Login" value="Login">
            </div>
        </form>
        <div class="link-page">
            <a class="btn-form" href="/register">Register</a>
        </div>
        <?php if (isset($error)): ?>
            <p class="note error"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

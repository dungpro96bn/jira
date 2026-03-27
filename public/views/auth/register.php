<?php require __DIR__ . '/../layouts/header.php'; ?>

<main id="login" class="register-page">
    <div class="login-main">
        <h2>Register</h2>
        <form method="POST" action="/register">
            <div class="group-field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="group-field">
                <label for="username">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="group-field">
                <label for="password">Password</label>
                <div class="container-input">
                    <input type="password" id="password-field" name="password" required>
                    <span toggle="#password-field" class="fa-regular fa-eye-slash field-icon toggle-password"></span>
                    <span id="generateBtn">Generate</span>
                </div>
            </div>
            <div class="group-field">
                <label for="username">Referral code</label>
                <input type="text" id="referral_code" name="referral_code" required>
            </div>
            <div class="submit-form">
                <input class="login-btn" type="submit" value="Register">
            </div>
        </form>
        <div class="link-page">
            <a class="btn-form" href="/login">Login</a>
        </div>
        <?php if (isset($success)): ?>
            <p class="note success">
                Register successful! Redirecting in <span id="count">5</span> seconds...
            </p>

            <script>
                let seconds = 5;
                let countdown = document.getElementById('count');

                let interval = setInterval(function() {
                    seconds--;
                    countdown.textContent = seconds;

                    if (seconds <= 0) {
                        clearInterval(interval);
                        window.location.href = "/login";
                    }
                }, 1000);
            </script>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p class="note error"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
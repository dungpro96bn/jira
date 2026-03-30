<?php require __DIR__ . '/../layouts/header.php'; ?>

    <main id="user-page">
        <div class="inner">

            <div class="container-panel">

                <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

                <div class="column-main">
                    <div class="user-container">
                    <div class="top-title">
                        <h2>Add New User</h2>
                    </div>

                    <div class="add-user-new">
                        <form autocomplete="off" method="POST" action="/user-new" id="formCreateUser">
                            <div class="group-field">
                                <label for="username">Username</label>
                                <div class="input-field">
                                    <input type="text" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="group-field">
                                <label for="username">Email</label>
                                <div class="input-field">
                                    <input type="email" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="group-field">
                                <label for="username">Role</label>
                                <div class="input-field">
                                    <select name="role" class="role-select-new">
                                        <option value="">Select role</option>
                                        <option value="admin">Admin</option>
                                        <option value="editor">Editor</option>
                                        <option value="user">User</option>
                                    </select>
                                </div>
                            </div>
                            <div class="group-field">
                                <label for="password">Password</label>
                                <div class="input-field">
                                    <div class="container-input">
                                        <input type="password" id="password-field" name="password" required>
                                        <span toggle="#password-field" class="fa-regular fa-eye-slash field-icon toggle-password"></span>
                                        <span id="generateBtn">Generate</span>
                                    </div>
                                </div>
                            </div>
                            <div class="group-field">
                                <label for="username">Referral code</label>
                                <div class="input-field">
                                    <input type="text" id="referral_code" name="referral_code" required>
                                </div>
                            </div>
                            <div class="submit-form">
                                <input class="login-btn" type="submit" value="Register">
                            </div>
                        </form>

                        <?php if (isset($success)): ?>
                            <p class="note success"><?php echo $success; ?></p>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <p class="note error"><?php echo $error; ?></p>
                        <?php endif; ?>

                    </div>

                </div>
                </div>

            </div>
        </div>
    </main>

<script>
    $(function () {
        setTimeout(function (){
            $('#formCreateUser input[type="text"]').val('');
            $('#formCreateUser input[type="password"]').val('');
            $('#formCreateUser input[type="email"]').val('');
        }, 1000)
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
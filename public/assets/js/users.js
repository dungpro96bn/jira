jQuery(function ($) {

    let alertTimer = null;

    // click Change Pass
    $(document).on('click', '.change-pass', function () {

        const $tr = $(this).closest('.user-row');
        const $input = $tr.find('.password-input');

        $input.val('');
        $input.prop('disabled', false);
        $input.focus();

        $tr.find('.save-pass, .cancel-pass').show();
        $(this).hide();
    });

    function resetPasswordUI($tr) {

        const $input = $tr.find('.password-input');

        $input.val('********');
        $input.prop('disabled', true);

        $tr.find('.change-pass').show();
        $tr.find('.save-pass, .cancel-pass').hide();
    }

    // Save password
    $(document).on('click', '.save-pass', function () {

        const $tr = $(this).closest('.user-row');
        const id = $tr.data('id');
        const $input = $tr.find('.password-input');
        const password = $input.val();

        if (!password) {
            alert('Please enter password');
            return;
        }

        $.post('/users/change-password', { id, password }, function (res) {

            if (res.success) {
                resetPasswordUI($tr);
                $('.alert .info-alert').html('<p class="ttl">Update password success</p>');
                $('.alert').addClass('is-open');
            } else {
                $('.alert .info-alert').html('<p class="ttl">Update password failed</p>');
                $('.alert').addClass('is-open');
            }

            if (alertTimer) {
                clearTimeout(alertTimer);
            }

            alertTimer = setTimeout(function () {
                $('.alert').removeClass('is-open');
            }, 3000);

        });
    });

    $(document).on('click', '.cancel-pass', function () {

        const $tr = $(this).closest('.user-row');

        resetPasswordUI($tr);
    });

    $(document).on('keydown', '.password-input', function (e) {
        if (e.key === 'Escape') {
            const $tr = $(this).closest('.user-row');
            resetPasswordUI($tr);
        }
    });

    $(document).on('keypress', '.password-input', function (e) {
        if (e.which === 13) {
            $(this).closest('.user-row').find('.save-pass').click();
        }
    });

    $(document).on('change', '.role-select', function () {

        const $tr = $(this).closest('.user-row');
        const id = $tr.data('id');
        const role = $(this).val();

        $.post('/users/update', {
            id: id,
            role: role
        }, function (res) {

            const data = typeof res === 'string' ? JSON.parse(res) : res;

            if (data.success) {
                $('.alert .info-alert').html('<p class="ttl">Update role success</p>');
                $('.alert').addClass('is-open');
            } else {
                $('.alert .info-alert').html('<p class="ttl">Update role failed ❌</p>');
                $('.alert').addClass('is-open');
            }

            if (alertTimer) {
                clearTimeout(alertTimer);
            }

            alertTimer = setTimeout(function () {
                $('.alert').removeClass('is-open');
            }, 3000);

        });
    });

    $(document).on('click', '.delete', function () {

        const $tr = $(this).closest('.user-row');
        const id = $tr.data('id');

        if (!confirm('Are you sure you want to delete this user?')) {
            return;
        }

        $.post('/users/delete', { id }, function (res) {

            const data = typeof res === 'string' ? JSON.parse(res) : res;

            if (data.success) {
                $tr.remove();
            } else {
                alert('Delete failed');
            }

        });
    });


    $(document).on('click', '.edit-user', function () {

        const $tr = $(this).closest('.user-row');

        const $username = $tr.find('.input-username');
        const $email = $tr.find('.input-email');

        // lưu giá trị cũ (để cancel)
        $username.data('old', $username.val());
        $email.data('old', $email.val());

        $username.prop('disabled', false);
        $email.prop('disabled', false);

        $tr.find('.save-user, .cancel-user').show();
        $(this).hide();
    });

    $(document).on('click', '.save-user', function () {

        const $tr = $(this).closest('.user-row');
        const id = $tr.data('id');

        const username = $tr.find('.input-username').val();
        const email = $tr.find('.input-email').val();

        if (!username || !email) {
            alert('Please fill all fields');
            return;
        }

        $.ajax({
            url: '/users/update',
            method: 'POST',
            data: { id, username, email },
            dataType: 'json',
            success: function (res) {

                if (res.success) {
                    resetUserUI($tr);
                    $('.alert .info-alert').html('<p class="ttl">Update success</p>');
                    $('.alert').addClass('is-open');
                } else {
                    $('.alert .info-alert').html('<p class="ttl">Update failed</p>');
                    $('.alert').addClass('is-open');
                }

                if (alertTimer) {
                    clearTimeout(alertTimer);
                }

                alertTimer = setTimeout(function () {
                    $('.alert').removeClass('is-open');
                }, 3000);

            }
        });
    });

    $(document).on('click', '.cancel-user', function () {

        const $tr = $(this).closest('.user-row');

        const $username = $tr.find('.input-username');
        const $email = $tr.find('.input-email');

        // revert lại
        $username.val($username.data('old'));
        $email.val($email.data('old'));

        resetUserUI($tr);
    });

    function resetUserUI($tr) {

        $tr.find('.input-username, .input-email').prop('disabled', true);

        $tr.find('.edit-user').show();
        $tr.find('.save-user, .cancel-user').hide();
    }

    $(document).on('keypress', '.input-username, .input-email', function (e) {
        if (e.which === 13) {
            $(this).closest('.user-row').find('.save-user').click();
        }
    });

});
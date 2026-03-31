jQuery(function ($) {

    /* ===============================
       Cache DOM
    =============================== */
    const $body = $("body");
    const $createTaskBox = $("#createTaskBox");
    const $formCreateTask = $("#formCreateTask");
    const $passwordField = $("#password-field");

    /* ===============================
       User dropdown (create task)
    =============================== */
    $body.on("click", ".form-createTask .user-active", function () {
        $(this).toggleClass("is-active")
            .siblings(".user-list")
            .toggleClass("is-open");
    });

    $body.on("click", ".form-createTask .user-item", function () {
        const userId = $(this).data("id");
        const userHtml = $(this).html();

        $(".form-createTask .user-item-active").html(userHtml);
        $("#assignee").val(userId);

        $(".form-createTask .user-active").removeClass("is-active");
        $(".form-createTask .user-list").removeClass("is-open");
    });


    /* ===============================
       Task child toggle
    =============================== */
    $body.on("click", ".btn-taskChild-list .btn-box", function () {
        const $parent = $(this).parent();
        $parent.toggleClass("active")
            .next()
            .toggleClass("active");
    });


    /* ===============================
       Open create task popup
    =============================== */
    $body.on("click", ".createTask", function () {
        $createTaskBox.addClass("is-open");
        $("#status").val($(this).data("status"));
    });

    $body.on("click", ".close-createTask, #createTaskBox .mask", function () {
        $createTaskBox.removeClass("is-open");
        $formCreateTask[0].reset();

        const select = document.querySelector('#labelSelect');

        if (select && select.tomselect) {
            select.tomselect.clear();
        }
    });

    /* ===============================
       URL param helper
    =============================== */
    function updateUrlParam(key, value) {
        const url = new URL(window.location.href);

        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }

        window.history.replaceState({}, "", url);
    }

    /* ===============================
       Open task popup
    =============================== */
    $body.on("click", ".open-task", function (e) {
        e.preventDefault();

        const target = $(this).attr("href").replace("#", "");
        const issueKey = $(this).data("issue-key");

        $("#" + target).addClass("is-open");

        updateUrlParam("selectedIssue", issueKey);
    });

    $body.on("click", ".taskContent-popup .close-popup", function () {
        $(".taskContent-popup").removeClass("is-open");
        updateUrlParam("selectedIssue");
    });

    /* ===============================
       Generate password
    =============================== */
    $("#generateBtn").on("click", function () {
        const length = 10;
        const charset = '!@#$%^&*()-_=+{}[];,.?~0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        const password = Array.from({ length }, () =>
            charset[Math.floor(Math.random() * charset.length)]
        ).join("");

        $passwordField.attr("type", "text").val(password);
    });

    /* ===============================
       Toggle password visibility
    =============================== */
    $body.on("click", ".toggle-password", function () {
        const $input = $($(this).attr("toggle"));
        const isPassword = $input.attr("type") === "password";

        $(this).toggleClass("fa-eye fa-eye-slash");
        $input.attr("type", isPassword ? "text" : "password");
    });


    // update change title task
    $(document).on('blur', '.input-summary', function () {

        let $input = $(this);

        let issueKey = $input.data('issue-key');
        let oldValue = $input.data('original');
        let newValue = $input.val().trim();

        if (newValue === oldValue) return;

        if (!newValue) {
            $input.val(oldValue);
            return;
        }

        $('.task-item .summary[data-issue-key="'+issueKey+'"]')
            .text(newValue);

        $.ajax({
            url: '/api/task/update-summary',
            method: 'POST',
            data: {
                issueKey: issueKey,
                summary: newValue
            },
            success: function (res) {

                if (res.success) {

                    // update lại original
                    $input.data('original', newValue);

                    // update UI ngoài board
                    $('.task-item .summary[data-issue-key="'+issueKey+'"]')
                        .text(newValue);

                }

            },
            error: function () {
                $input.val(oldValue);
            }
        });
    });


    // click để mở date picker
    $body.on('click', '.dueDate', function (e) {
        const $wrap = $(this);
        const $input = $wrap.find('.dueDate-input');

        $input.addClass("is-open");
        $wrap.find('.clear-due-date').addClass("is-open");

        e.stopPropagation(); // chặn bubble

        $input[0].showPicker?.(); // modern browser
    });

    $(document).on('click', function () {
        $('.dueDate-input').removeClass('is-open');
        $('.clear-due-date').removeClass('is-open');
    });

    // chọn xong → update
    $body.on('change', '.dueDate-input', function () {
        const $input = $(this);
        const newDate = $input.val();

        const $wrap = $input.closest('.dueDate');
        const issueKey = $wrap.data('issue-key');
        const dueDateTask = $('.open-task .dueDate[data-issue-key="'+issueKey+'"]');
        const svgDeadline = '<svg width="14" height="14" fill="none" viewBox="0 0 16 16" role="presentation" class="svg-deadline">\n' +
                                       '<path fill="currentcolor" fill-rule="evenodd" d="M5.7 1.383c.996-1.816 3.605-1.817 4.602-.002l5.35 9.73C16.612 12.86 15.346 15 13.35 15H2.667C.67 15-.594 12.862.365 11.113zm3.288.72a1.125 1.125 0 0 0-1.972.002L1.68 11.834c-.41.75.132 1.666.987 1.666H13.35c.855 0 1.398-.917.986-1.667z" clip-rule="evenodd"></path>\n' +
                                       '<path fill="currentcolor" fill-rule="evenodd" d="M7.25 9V4h1.5v5z" clip-rule="evenodd"></path>\n' +
                                       '<path fill="currentcolor" d="M9 11.25a1 1 0 1 1-2 0 1 1 0 0 1 2 0"></path>\n' +
                                   '</svg>';

        $.ajax({
            url: '/task/update-due-date',
            method: 'POST',
            data: {
                issueKey: issueKey,
                duedate: newDate
            },
            success: function (res) {
                if (res.success) {

                    //format: May 13, 2026
                    const date = new Date(newDate);
                    const today = new Date();

                    let formatted;

                    // optional: giống Jira
                    const isToday = date.toDateString() === today.toDateString();

                    const tomorrow = new Date();
                    tomorrow.setDate(today.getDate() + 1);

                    // const isTomorrow = date.toDateString() === tomorrow.toDateString();

                    formatted = date.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    });

                    // update text UI
                    $wrap.find('span').text(formatted);

                    // update lại data-date (quan trọng)
                    $wrap.attr('data-date', newDate);

                    const $checkDate = $wrap.find('span').text();

                    if(dueDateTask.find('span').length > 0){
                        dueDateTask.find('span').text(formatted);
                    } else {
                        $('<p>\n' +
                            '<svg width="16" height="16" class="svg-no-deadline" viewBox="0 0 24 24" role="presentation">\n' +
                            '    <path d="M4.995 5h14.01C20.107 5 21 5.895 21 6.994v12.012A1.994 1.994 0 0119.005 21H4.995A1.995 1.995 0 013 19.006V6.994C3 5.893 3.892 5 4.995 5zM5 9v9a1 1 0 001 1h12a1 1 0 001-1V9H5zm1-5a1 1 0 012 0v1H6V4zm10 0a1 1 0 012 0v1h-2V4zm-9 9v-2.001h2V13H7zm8 0v-2.001h2V13h-2zm-4 0v-2.001h2.001V13H11zm-4 4v-2h2v2H7zm4 0v-2h2.001v2H11zm4 0v-2h2v2h-2z" fill="currentColor" fill-rule="evenodd"></path>\n' +
                            '</svg>\n' +
                            '<span>'+ formatted +'</span>\n' +
                            '</p>').appendTo($(dueDateTask));
                    }

                    // update class deadline
                    const todayStr = today.toISOString().split('T')[0];

                    $input.removeClass("is-open");
                    $input.next().removeClass("is-open");

                    $('<button type="button" class="clear-due-date">✕</button>').insertAfter($input);

                    if (newDate <= todayStr) {
                        $wrap.addClass('deadline');
                        if($wrap.find('.svg-deadline').length < 1){
                            $(svgDeadline).insertBefore($wrap.find('.svg-no-deadline'));
                        }
                        $wrap.find('.svg-deadline').show();

                        dueDateTask.addClass('deadline');
                        if(dueDateTask.find('.svg-deadline').length < 1){
                            $(svgDeadline).insertBefore(dueDateTask.find('.svg-no-deadline'));
                        }
                        dueDateTask.find('.svg-deadline').show();
                    } else {
                        $wrap.removeClass('deadline');
                        $wrap.find('.svg-deadline').hide();
                        dueDateTask.removeClass('deadline');
                        dueDateTask.find('.svg-deadline').hide();
                    }
                }
            }
        });
    });


    $body.on('click', '.clear-due-date', function (e) {
        e.stopPropagation();

        const $button = $(this);
        const $wrap = $(this).closest('.dueDate');
        const issueKey = $wrap.data('issue-key');
        const dueDateTask = $('.open-task .dueDate[data-issue-key="'+issueKey+'"]');

        $.ajax({
            url: '/task/update-due-date',
            method: 'POST',
            data: {
                issueKey: issueKey,
                duedate: ''
            },
            success: function (res) {
                if (res.success) {

                    // reset UI
                    $wrap.find('span').text('');

                    $wrap.removeClass('deadline');
                    $wrap.find('.svg-deadline').hide();

                    // reset input
                    $wrap.find('.dueDate-input').val('');

                    $button.removeClass("is-open");

                    dueDateTask.find('p').remove();
                    dueDateTask.find('.svg-deadline').hide();

                }
            }
        });
    });

    let alertTimer = null;

    // copy link task
    $body.on('click', '.btn-copy-link', function (e) {
        e.stopPropagation();

        const $wrap = $(this).closest('.more-list');
        const issueKey = $wrap.attr('data-issue-key');
        const baseUrl = 'https://dev-scvweb.atlassian.net/browse/';

        const link = baseUrl + issueKey;

        navigator.clipboard.writeText(link).then(() => {

            $('.more-list').removeClass('is-open');
            $('.more-actions').removeClass('is-active');

            $('.alert .info-alert').html('<p class="ttl">Link copied</p>');
            $('.alert').addClass('is-open');

            // clear timer cũ trước
            if (alertTimer) {
                clearTimeout(alertTimer);
            }

            alertTimer = setTimeout(function () {
                $('.alert').removeClass('is-open');
            }, 3000);
        });
    });


    // delete tassk
    $body.on('click', '.btn-delete-task', function (e) {
        e.stopPropagation();

        const $wrap = $(this).closest('.more-list');
        const issueKey = $wrap.attr('data-issue-key');

        console.log(issueKey);

        if (!confirm('Delete task '+ issueKey)) {
            return;
        }

        $('.more-list').removeClass('is-open');
        $('.more-actions').removeClass('is-active');

        $.ajax({
            url: '/task/delete',
            method: 'POST',
            data: {
                issueKey: issueKey
            },
            success: function (res) {
                if (res.success) {

                    //remove task khỏi UI (board/list)
                    $(`[data-issue-key="${issueKey}"]`).closest('.task-item').remove();

                    $('.alert .info-alert').html('<p class="ttl">Archived task '+ issueKey +'</p><p>Work item archived successfully</p>');
                    $('.alert').addClass('is-open');

                    // clear timer cũ trước
                    if (alertTimer) {
                        clearTimeout(alertTimer);
                    }

                    alertTimer = setTimeout(function () {
                        $('.alert').removeClass('is-open');
                    }, 3000);

                } else {
                    alert("khong xoa được");
                }
            }
        });
    });

    // close popup alert
    $body.on('click', '.alert .btn-close', function (e) {
        e.stopPropagation();

        $('.alert').removeClass('is-open');

        // clear luôn timer
        if (alertTimer) {
            clearTimeout(alertTimer);
        }
    });


    // open more actions
    const $moreList = $('.more-list');

    $body.on('click', '.more-actions', function (e) {
        e.stopPropagation();
        $('.more-actions').removeClass('is-active');

        const $btn = $(this);
        const issueKey = $btn.data('issue-key');

        const rect = this.getBoundingClientRect();

        $btn.addClass("is-active");

        // show tạm để tính size
        $moreList.addClass('is-open');

        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        const menuWidth = $moreList.outerWidth();
        const menuHeight = $moreList.outerHeight();

        let left = rect.left + 30;
        let top = rect.top;

        // =========================
        // tránh tràn phải
        // =========================
        if (left + menuWidth > windowWidth) {
            left = windowWidth - menuWidth - 10;
        }

        // =========================
        // tránh tràn trái
        // =========================
        if (left < 10) {
            left = 10;
        }

        // =========================
        // nếu gần đáy → lật lên trên
        // =========================
        if (top + menuHeight > windowHeight) {
            top = rect.top - menuHeight - 5;
        }

        // set vị trí
        $moreList.css({
            top: top,
            left: left
        });

        // gán issueKey để dùng cho action
        $moreList.attr('data-issue-key', issueKey);
    });

    $(document).on('click', function () {
        $moreList.removeClass('is-open');
        $('.more-actions').removeClass('is-active');
    });

    $body.on('click', '.more-list', function (e) {
        e.stopPropagation();
    });


    $(document).on('click', '.priority-selected', function (e) {
        e.stopPropagation();

        let dropdown = $(this).next('.priority-dropdown');

        $('.priority-dropdown').not(dropdown).addClass('hidden');
        dropdown.toggleClass('hidden');
    });

    $(document).on('click', '.priority-option', function () {

        debugger;
        let wrapper = $(this).closest('.priority-wrapper');
        let issueKey = $(this).closest('.right-taskInfo').data('issue-key');

        let name = $(this).data('name');
        let img = $(this).find('img').attr('src');

        // 👉 call API
        $.ajax({
            url: '/api/task/priority',
            method: 'POST',
            data: {
                issueKey: issueKey,
                priority: name
            },
            success: function (res) {
                $('.icon-priority[data-issue-key="'+issueKey+'"] img').attr("src", img);
            }
        });

        // update UI
        wrapper.find('.priority-selected .priority-name').text(name);
        wrapper.find('.priority-selected .icon img').attr('src', img);

        // close dropdown
        wrapper.find('.priority-dropdown').addClass('hidden');
    });

    $(document).on('click', function () {
        $('.priority-dropdown').addClass('hidden');
    });


    // document.querySelectorAll('.details-item.labels').forEach(wrapper => {
    //
    //     const view = wrapper.querySelector('.label-view');
    //     const edit = wrapper.querySelector('.label-edit');
    //     const select = wrapper.querySelector('.allLabelsSelect');
    //
    //     if (!select) return;
    //
    //     //init TomSelect
    //     if (select.tomselect) {
    //         select.tomselect.destroy();
    //     }
    //
    //     const ts = new TomSelect(select, {
    //         plugins: ['remove_button'],
    //         persist: false,
    //         create: true,
    //         maxItems: null,
    //         placeholder: "Select labels..."
    //     });
    //
    //     // =========================
    //     // CLICK → SHOW SELECT
    //     // =========================
    //     view.addEventListener('click', () => {
    //         view.style.display = 'none';
    //         edit.style.display = 'flex';
    //
    //         ts.focus();
    //     });
    //
    //     // =========================
    //     // CHANGE → UPDATE API
    //     // =========================
    //     select.addEventListener('change', function () {
    //
    //         const issueKey = this.dataset.issueKey;
    //         const labels = ts.getValue(); // array
    //
    //         console.log('Update labels:', labels);
    //
    //         $.ajax({
    //             url: '/api/task/update-labels',
    //             method: 'POST',
    //             data: {
    //                 issueKey: issueKey,
    //                 labels: labels
    //             },
    //             success: function (res) {
    //
    //                 if (!res.success) {
    //                     console.error(res.error);
    //                     return;
    //                 }
    //
    //                 // render lại labels
    //                 renderLabels(view, labels);
    //
    //                 view.style.display = 'block';
    //                 edit.style.display = 'none';
    //             }
    //         });
    //     });
    //
    // });
    //
    //
    // function renderLabels(container, labels) {
    //
    //     if (!labels.length) {
    //         container.innerHTML = '<span class="empty">none</span>';
    //         return;
    //     }
    //
    //     let html = '';
    //
    //     labels.forEach(label => {
    //         html += `<span class="label-item">${label}</span>`;
    //     });
    //
    //     container.innerHTML = html;
    // }


    const observer = new MutationObserver(() => {

        document.querySelectorAll('.allLabelsSelect').forEach(select => {

            // nếu đã init rồi thì bỏ qua
            if (select.tomselect) return;

            new TomSelect(select, {
                plugins: ['remove_button'],
                persist: false,
                create: true,
                maxItems: null,
                placeholder: "Select labels..."
            });

        });

    });


    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    document.addEventListener('click', function (e) {

        const view = e.target.closest('.label-view');

        if (!view) return;

        const wrapper = view.closest('.details-item.labels');
        const edit = wrapper.querySelector('.label-edit');
        const select = wrapper.querySelector('.allLabelsSelect');

        if (!select || !select.tomselect) return;

        view.style.display = 'none';
        edit.style.display = 'flex';

        select.tomselect.focus();
    });

    document.addEventListener('change', function (e) {

        if (!e.target.classList.contains('allLabelsSelect')) return;

        const select = e.target;
        const wrapper = select.closest('.details-item.labels');
        const view = wrapper.querySelector('.label-view');

        const issueKey = select.dataset.issueKey;
        const labels = select.tomselect.getValue();

        $.ajax({
            url: '/api/task/update-labels',
            method: 'POST',
            data: {
                issueKey: issueKey,
                labels: labels
            },
            success: function (res) {

                if (!res.success) {
                    console.error(res.error);
                    return;
                }

                renderLabels(view, labels);

                view.style.display = 'flex';
                wrapper.querySelector('.label-edit').style.display = 'none';

                const labelView = $(view).html();

                $('.label-list[data-issue-key="'+issueKey+'"]').html(labelView);

            }
        });

    });

    function renderLabels(container, labels) {

        if (!labels.length) {
            container.innerHTML = '<span class="empty">none</span>';
            return;
        }

        let html = '';

        labels.forEach(label => {
            html += `<span class="label-item">${label}</span>`;
        });

        container.innerHTML = html;
    }


});
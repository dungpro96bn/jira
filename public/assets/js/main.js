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

});
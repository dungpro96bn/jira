<?php require __DIR__ . '/../layouts/header.php'; ?>

    <div class="app dashboard-app-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main dashboard-board-page">
        <section class="dashboard-topbar">
            <div class="dashboard-heading-block">
                <h2>Board</h2>
            </div>
            <div class="dashboard-topbar-actions">
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/dashboard" class="dashboard-button">Dashboard</a>
                <?php endif; ?>
                <a href="/create-task" class="dashboard-button dashboard-button-primary">+ Create Task</a>
            </div>
        </section>

        <section class="panel board-dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Kanban Board</h3>
                    <p class="panel-subtitle">Drag and drop tasks between columns, then open details without leaving the page.</p>
                </div>
            </div>
            <div class="panel-body board-dashboard-body">
                <div class="board-shell">
                    <div id="board-list" class="board-list"></div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>

        /* ===============================
        Auto open popup if URL has selectedIssue
        ================================ */
        function openPopupFromUrl() {
            const url = new URL(window.location.href);
            const selectedIssue = url.searchParams.get("selectedIssue");

            if (!selectedIssue) return;

            const $trigger = $('a.open-task[data-issue-key="' + selectedIssue + '"]');

            if ($trigger.length) {
                const target = $trigger.attr("href").replace("#", "");
                $("#" + target).addClass("is-open");
            }
        }

        function updateUrlParam(key, value) {
            const url = new URL(window.location.href);

            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }

            window.history.replaceState({}, "", url);
        }

        function loadBoard() {
            $.get('/api/board', function (data) {
                $('#board-list').html(data);
                initSortable();
                $(".checkLoad").removeClass("is-open");
                openPopupFromUrl();
            });
        }

        // open popup task
        const editorInitialContent = {};
        document.addEventListener("click", function (e) {
            const btn = e.target.closest(".open-task-child");
            if (!btn) return;

            $(".checkLoad").addClass("is-open");

            e.preventDefault();

            const taskId = btn.dataset.id;
            const issueId = btn.dataset.issueKey;

            updateUrlParam("selectedIssue", issueId);

            fetch("/task/detail?id=" + taskId)
                .then(res => res.text())
                .then(html => {
                    document.getElementById("task-popup-content").innerHTML = html;
                    $("#task-popup").addClass("is-open");

                    // remove editor cũ
                    tinymce.remove();

                    // init lại + gắn event luôn tại đây
                    tinymce.init({
                        selector: '.tinymce-editor',
                        height: 450,
                        menubar: false,
                        plugins: 'image advlist autolink lists link charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
                        toolbar: 'bold italic | undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image | help',
                        branding: false,

                        setup: function (editor) {
                            let initialContent = '';

                            editor.on('init', function () {
                                initialContent = editor.getContent();
                                editorInitialContent[editor.id] = initialContent;
                            });

                            editor.on('change keyup paste', function () {
                                const currentContent = editor.getContent();

                                if (currentContent !== initialContent) {
                                    const $popup = $(editor.getElement()).closest('.taskContent-popup');

                                    $popup.find('.btn-update-description').removeClass('hidden');
                                }
                            });
                        }
                    });

                    $(".checkLoad").removeClass("is-open");
                });
        });

        $(document).ready(function () {
            $(".checkLoad").addClass("is-open");
            loadBoard();
        });

        $(document).on('click', '.btn-cancel', function () {

            const container = $(this).closest('.taskContent-popup'); // chỉnh lại class
            const textareaId = container.find('.tinymce-editor').attr('id');

            const editor = tinymce.get(textareaId);

            if (editor && editorInitialContent[textareaId] !== undefined) {
                editor.setContent(editorInitialContent[textareaId]);
            }
            $(this).closest('.taskContent-popup').find('.btn-update-description').addClass("hidden");
        });

    </script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
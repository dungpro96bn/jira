<?php require __DIR__ . '/../layouts/header.php'; ?>

    <div class="app dashboard-app-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main dashboard-board-page">
        <section class="dashboard-topbar">
            <div class="dashboard-heading-block">
                <h2>Tasks</h2>
            </div>
            <div class="dashboard-topbar-actions">
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/dashboard" class="dashboard-button">Dashboard</a>
                <?php endif; ?>
                <a href="/create-task" class="dashboard-button dashboard-button-primary"><i class="fa-solid fa-plus"></i>Create Task</a>
            </div>
        </section>



        <?php
            $boardStatuses = [];
            $boardPriorities = [];
            $boardAssigneeMap = [];
            foreach (($issues ?? []) as $boardIssue) {
                $statusName = $boardIssue['fields']['status']['name'] ?? '';
                if ($statusName) { $boardStatuses[$statusName] = $statusName; }
                $priorityName = $boardIssue['fields']['priority']['name'] ?? '';
                if ($priorityName) { $boardPriorities[$priorityName] = $priorityName; }
                if (!empty($boardIssue['fields']['assignee'])) {
                    $a = $boardIssue['fields']['assignee'];
                    $boardAssigneeMap[$a['accountId']] = [
                        'id' => $a['accountId'],
                        'name' => $a['displayName'] ?? 'Unknown',
                        'avatar' => $a['avatarUrls']['48x48'] ?? ''
                    ];
                }
            }
            foreach (($users ?? []) as $boardUser) {
                if (!empty($boardUser['accountId']) && empty($boardAssigneeMap[$boardUser['accountId']])) {
                    $boardAssigneeMap[$boardUser['accountId']] = [
                        'id' => $boardUser['accountId'],
                        'name' => $boardUser['displayName'] ?? 'Unknown',
                        'avatar' => $boardUser['avatarUrls']['48x48'] ?? ''
                    ];
                }
            }
        ?>



        <div class="board-filter-popup" id="boardFilterPopup" hidden>
            <div class="board-filter-popup__inner panel">
                <div class="board-filter-popup__header">
                    <h3>Filters</h3>
                    <button type="button" class="board-filter-close" id="boardFilterClose"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="board-filter-group">
                    <h4>Date range</h4>
                    <div class="board-filter-grid board-filter-grid--dates">
                        <label><span>Start date</span><input type="date" id="filterDueFrom"></label>
                        <label><span>Due date</span><input type="date" id="filterDueTo"></label>
                    </div>
                </div>
                <div class="board-filter-group">
                    <h4>Assignee</h4>
                    <div class="board-filter-assignees" id="boardAssigneeFilterList">
                        <?php foreach ($boardAssigneeMap as $boardUser): ?>
                            <button
                                type="button"
                                class="board-filter-avatar"
                                data-account-id="<?= htmlspecialchars($boardUser['id']) ?>"
                                title="<?= htmlspecialchars($boardUser['name']) ?>"
                            >
                                <?php if (!empty($boardUser['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($boardUser['avatar']) ?>" alt="<?= htmlspecialchars($boardUser['name']) ?>">
                                <?php else: ?>
                                    <span><?= htmlspecialchars(mb_strtoupper(mb_substr($boardUser['name'], 0, 1))) ?></span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="board-filter-group">
                    <h4>Created</h4>
                    <div class="board-filter-grid board-filter-grid--dates">
                        <label><span>From</span><input type="date" id="filterCreatedFrom"></label>
                        <label><span>To</span><input type="date" id="filterCreatedTo"></label>
                    </div>
                </div>
                <div class="board-filter-group">
                    <h4>Priority</h4>
                    <div class="board-filter-pills" id="boardPriorityFilterList">
                        <?php foreach ($boardPriorities as $priority): ?>
                            <button type="button" class="board-filter-pill" data-priority="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars($priority) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="board-filter-group">
                    <h4>Status</h4>
                    <div class="board-filter-pills" id="boardStatusFilterList">
                        <?php foreach ($boardStatuses as $status): ?>
                            <button type="button" class="board-filter-pill board-filter-pill--status" data-status="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="board-filter-actions">
                    <button type="button" class="dashboard-button" id="boardFilterReset">Reset</button>
                    <button type="button" class="dashboard-button dashboard-button-primary" id="boardFilterApply">Apply</button>
                </div>
            </div>
        </div>

        <section class="panel board-dashboard-panel">
            <section class="board-filters-bar">
                <div class="board-filters-bar__search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="boardSearchInput" placeholder="Search board">
                </div>
                <div class="board-filters-bar__assignees" id="boardQuickAssignees">
                    <?php
                    $total = count($boardAssigneeMap);
                    $i = 0;
                    ?>

                    <?php foreach ($boardAssigneeMap as $boardUser): ?>
                        <button
                                type="button"
                                class="board-assignee-chip"
                                data-account-id="<?= htmlspecialchars($boardUser['id']) ?>"
                                title="<?= htmlspecialchars($boardUser['name']) ?>"
                                style="z-index: <?= $total - $i ?>"
                        >
                            <?php if (!empty($boardUser['avatar'])): ?>
                                <img src="<?= htmlspecialchars($boardUser['avatar']) ?>" alt="<?= htmlspecialchars($boardUser['name']) ?>">
                            <?php else: ?>
                                <span><?= htmlspecialchars(mb_strtoupper(mb_substr($boardUser['name'], 0, 1))) ?></span>
                            <?php endif; ?>
                        </button>
                        <?php $i++; endforeach; ?>
                </div>
                <div class="board-filters-bar__actions">
                    <button type="button" class="dashboard-button" id="boardFilterToggle">Filter <i class="fa-solid fa-angle-down"></i></button>
                </div>
            </section>
            <div class="panel-body board-dashboard-body">
                <div class="board-shell">
                    <div id="board-list" class="board-list"></div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>

        function updateUrlParam(key, value) {
            const url = new URL(window.location.href);

            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }

            window.history.replaceState({}, "", url);
        }

        function initPopupEditor() {
            if (typeof tinymce === 'undefined') {
                return;
            }

            tinymce.remove();

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

            if (typeof window.initDashboardLabelSelects === 'function') {
                window.initDashboardLabelSelects();
            }
        }

        function openPopupFromUrl() {
            const url = new URL(window.location.href);
            const selectedIssue = url.searchParams.get("selectedIssue");

            if (!selectedIssue) return;

            const $trigger = $('.open-task[data-issue-key="' + selectedIssue + '"], .open-task-child[data-issue-key="' + selectedIssue + '"]').first();

            if ($trigger.length) {
                $trigger.trigger('click');
            }
        }

        function loadTaskPopupById(taskId, issueKey, skipHistory) {
            if (!taskId || !issueKey) {
                return;
            }

            $(".checkLoad").addClass("is-open");

            if (!skipHistory) {
                updateUrlParam("selectedIssue", issueKey);
            }

            fetch("/task/detail?id=" + encodeURIComponent(taskId))
                .then(res => res.text())
                .then(html => {
                    document.getElementById("task-popup-content").innerHTML = html;
                    $("#task-popup").addClass("is-open");
                    initPopupEditor();
                })
                .catch(() => {
                    alert('Unable to load task details.');
                })
                .finally(() => {
                    $(".checkLoad").removeClass("is-open");
                });
        }

        window.loadTaskPopupById = loadTaskPopupById;

        const boardFilterState = {
            search: '',
            dueFrom: '',
            dueTo: '',
            createdFrom: '',
            createdTo: '',
            assignees: [],
            priorities: [],
            statuses: []
        };

        function toggleBoardFilterValue(collection, value) {
            const index = collection.indexOf(value);
            if (index >= 0) {
                collection.splice(index, 1);
            } else {
                collection.push(value);
            }
        }

        function syncBoardFilterUi() {
            $('#boardSearchInput').val(boardFilterState.search);
            $('#filterDueFrom').val(boardFilterState.dueFrom);
            $('#filterDueTo').val(boardFilterState.dueTo);
            $('#filterCreatedFrom').val(boardFilterState.createdFrom);
            $('#filterCreatedTo').val(boardFilterState.createdTo);

            $('.board-assignee-chip, .board-filter-avatar').each(function () {
                const isActive = boardFilterState.assignees.indexOf($(this).data('account-id')) >= 0;
                $(this).toggleClass('is-active', isActive);
            });

            $('.board-filter-pill[data-priority]').each(function () {
                const isActive = boardFilterState.priorities.indexOf($(this).data('priority')) >= 0;
                $(this).toggleClass('is-active', isActive);
            });

            $('.board-filter-pill[data-status]').each(function () {
                const isActive = boardFilterState.statuses.indexOf($(this).data('status')) >= 0;
                $(this).toggleClass('is-active', isActive);
            });
        }

        function dateInRange(value, from, to) {
            if (!value) {
                return !from && !to;
            }
            if (from && value < from) return false;
            if (to && value > to) return false;
            return true;
        }

        function updateVisibleTaskCounts() {
            $('.board-item').each(function () {
                const count = $(this).find('.task-item:visible').length;
                $(this).find('.task-count').first().text(count);
            });
        }

        function applyBoardFilters() {
            const search = (boardFilterState.search || '').toLowerCase();

            $('.task-item').each(function () {
                const $task = $(this);
                const taskSearch = String($task.data('search') || '').toLowerCase();
                const assigneeId = String($task.data('assignee-id') || '');
                const priority = String($task.data('priority') || '');
                const status = String($task.data('status') || '');
                const created = String($task.data('created') || '');
                const due = String($task.data('due') || '');

                let visible = true;

                if (search && taskSearch.indexOf(search) === -1) {
                    visible = false;
                }

                if (visible && boardFilterState.assignees.length && boardFilterState.assignees.indexOf(assigneeId) === -1) {
                    visible = false;
                }

                if (visible && boardFilterState.priorities.length && boardFilterState.priorities.indexOf(priority) === -1) {
                    visible = false;
                }

                if (visible && boardFilterState.statuses.length && boardFilterState.statuses.indexOf(status) === -1) {
                    visible = false;
                }

                if (visible && !dateInRange(created, boardFilterState.createdFrom, boardFilterState.createdTo)) {
                    visible = false;
                }

                if (visible && !dateInRange(due, boardFilterState.dueFrom, boardFilterState.dueTo)) {
                    visible = false;
                }

                $task.toggle(visible);
            });

            updateVisibleTaskCounts();
            syncBoardFilterUi();
        }

        function resetBoardFilters() {
            boardFilterState.search = '';
            boardFilterState.dueFrom = '';
            boardFilterState.dueTo = '';
            boardFilterState.createdFrom = '';
            boardFilterState.createdTo = '';
            boardFilterState.assignees = [];
            boardFilterState.priorities = [];
            boardFilterState.statuses = [];
            applyBoardFilters();
        }

        function loadBoard() {
            $.get('/api/board', function (data) {
                $('#board-list').html(data);
                initSortable();
                applyBoardFilters();
                $(".checkLoad").removeClass("is-open");
                openPopupFromUrl();
            });
        }

        $(document).on('click', '.open-task, .open-task-child', function (e) {
            e.preventDefault();

            const taskId = $(this).data('id');
            const issueKey = $(this).data('issue-key');

            loadTaskPopupById(taskId, issueKey);
        });

        const editorInitialContent = {};

        window.loadBoard = loadBoard;

        $(document).on('input', '#boardSearchInput', function () {
            boardFilterState.search = $(this).val().trim();
            applyBoardFilters();
        });

        $(document).on('click', '#boardFilterToggle', function () {
            $('#boardFilterPopup').prop('hidden', false).addClass('is-open');
        });

        $(document).on('click', '#boardFilterClose', function () {
            $('#boardFilterPopup').removeClass('is-open').prop('hidden', true);
        });

        $(document).on('click', '#boardFilterPopup', function (e) {
            if ($(e.target).is('#boardFilterPopup')) {
                $('#boardFilterPopup').removeClass('is-open').prop('hidden', true);
            }
        });

        $(document).on('click', '.board-assignee-chip, .board-filter-avatar', function () {
            const accountId = $(this).data('account-id');
            if (!accountId) return;
            toggleBoardFilterValue(boardFilterState.assignees, accountId);
            applyBoardFilters();
        });

        $(document).on('click', '.board-filter-pill[data-priority]', function () {
            const priority = $(this).data('priority');
            toggleBoardFilterValue(boardFilterState.priorities, priority);
            applyBoardFilters();
        });

        $(document).on('click', '.board-filter-pill[data-status]', function () {
            const status = $(this).data('status');
            toggleBoardFilterValue(boardFilterState.statuses, status);
            applyBoardFilters();
        });

        $(document).on('change', '#filterDueFrom, #filterDueTo, #filterCreatedFrom, #filterCreatedTo', function () {
            boardFilterState.dueFrom = $('#filterDueFrom').val();
            boardFilterState.dueTo = $('#filterDueTo').val();
            boardFilterState.createdFrom = $('#filterCreatedFrom').val();
            boardFilterState.createdTo = $('#filterCreatedTo').val();
            applyBoardFilters();
        });

        $(document).on('click', '#boardFilterReset', function () {
            resetBoardFilters();
        });

        $(document).on('click', '#boardFilterApply', function () {
            $('#boardFilterPopup').removeClass('is-open').prop('hidden', true);
            applyBoardFilters();
        });

        $(document).ready(function () {
            syncBoardFilterUi();
            $(".checkLoad").addClass("is-open");
            loadBoard();
        });

        $(document).on('click', '.btn-cancel', function () {

            const container = $(this).closest('.taskContent-popup');
            const textareaId = container.find('.tinymce-editor').attr('id');

            const editor = tinymce.get(textareaId);

            if (editor && editorInitialContent[textareaId] !== undefined) {
                editor.setContent(editorInitialContent[textareaId]);
            }
            $(this).closest('.taskContent-popup').find('.btn-update-description').addClass("hidden");
        });

    </script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

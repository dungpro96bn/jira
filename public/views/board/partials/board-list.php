<?php

$issues = $issues ?? [];

$parents = [];
$children = [];

foreach ($issues as $issue) {

    if (!empty($issue['fields']['parent'])) {

        // Đây là subtask
        $parentId = $issue['fields']['parent']['id'];

        $children[$parentId][] = $issue;

    } else {

        // Đây là parent
        $parents[] = $issue;
    }
}

$columnOrder = [
    "To Do",
    "In Progress",
    "VNCHECK",
    "FIX",
    "JPCHECK",
    "Done",
    "Delivered",
    "PENDING"
];

$columns = [];

foreach ($columnOrder as $statusName) {

    $columns[$statusName] = [
        'id' => strtolower(str_replace(' ', '-', $statusName)),
        'color' => '#172b4d',
        'bg' => '#DFE1E6'
    ];
}

foreach ($issues as $issue) {

    $status = $issue['fields']['status']['name'];

    if (!isset($columns[$status])) {
        $columns[$status] = [
            'id' => strtolower(str_replace(' ', '-', $status)),
            'color' => '#172b4d',
            'bg' => '#DFE1E6'
        ];
    }
}

$statusMap = [
    'To Do' => '10095',
    'In Progress' => '10096',
    'Done' => '10097',
    'VNCHECK' => '10173',
    'FIX' => '10174',
    'JPCHECK' => '10175',
    'Delivered' => '10208',
    'PENDING' => '10209'
];

foreach ($issues as $issue) {
    $name = $issue['fields']['status']['name'];
    $id   = $issue['fields']['status']['id'];

    $statusMap[$name] = $id;
}

?>


<?php foreach ($columns as $statusName => $column): ?>

    <?php
    $issuesInColumn = array_filter($parents, function($issue) use ($statusName){
        return $issue['fields']['status']['name'] === $statusName;
    });

    $taskCount = count($issuesInColumn);

    ?>

    <div data-status="<?= $statusName ?>" id="<?= $column['id'] ?>" class="board-item board-<?= $column['id'] ?>">

        <p class="title-column">
            <span><?= $statusName ?></span>
            <span class="task-count"><?= $taskCount ?></span>
        </p>

        <div class="board-column">
            <div class="boardContent-list" data-status="<?= $statusName ?>" data-status-id="<?= $statusMap[$statusName] ?? '' ?>">
                <?php foreach ($issuesInColumn as $issue): ?>
                    <?php
                    $issueId = $issue['id'];
                    $childIssues = $children[$issueId] ?? [];
                    $childCount = count($childIssues);
                    ?>
                    <div class="task-item"
                         data-issue-key="<?= htmlspecialchars($issue['key']) ?>"
                         data-task-id="<?= htmlspecialchars($issueId) ?>"
                         data-status="<?= htmlspecialchars($issue['fields']['status']['name'] ?? '') ?>"
                         data-priority="<?= htmlspecialchars($issue['fields']['priority']['name'] ?? '') ?>"
                         data-assignee-id="<?= htmlspecialchars($issue['fields']['assignee']['accountId'] ?? '') ?>"
                         data-assignee-name="<?= htmlspecialchars($issue['fields']['assignee']['displayName'] ?? 'Unassigned') ?>"
                         data-created="<?= htmlspecialchars(!empty($issue['fields']['created']) ? substr($issue['fields']['created'], 0, 10) : '') ?>"
                         data-due="<?= htmlspecialchars($issue['fields']['duedate'] ?? '') ?>"
                         data-search="<?= htmlspecialchars(strtolower(trim(($issue['key'] ?? '') . ' ' . ($issue['fields']['summary'] ?? '') . ' ' . implode(' ', $issue['fields']['labels'] ?? [])))) ?>">
                        <div class="more-actions" data-issue-key="<?= $issue['key'] ?>">
                            <svg fill="none" viewBox="0 0 16 16" role="presentation">
                                <path fill="currentcolor" fill-rule="evenodd" d="M0 8a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0m6.5 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0M13 8a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="taskItem-inner">
                            <a class="open-task" data-id="<?= $issueId ?>" data-issue-key="<?= $issue['key'] ?>" href="#">
                                <h4 class="board-summary summary" data-issue-key="<?= $issue['key'] ?>">
                                    <?= htmlspecialchars($issue['fields']['summary']) ?>
                                </h4>

                                <?php $labels = $issue['fields']['labels']; ?>
                                <div class="label-list" data-issue-key="<?= $issue['key'] ?>">
                                    <?php foreach ($labels as $label) :?>
                                        <span class="label-item"><?= $label ?></span>
                                    <?php endforeach;?>
                                </div>


                                <?php if (isset($issue['fields']['duedate'])): ?>
                                    <?php
                                    $currentDate = date('Y-m-d');
                                    $dateString = $issue['fields']['duedate'];
                                    $date = date_create_from_format('Y-m-d', $dateString);
                                    $formattedDate = date_format($date, 'F j, Y');
                                    ?>
                                    <div class="dueDate pointer-events-none <?php if ($currentDate >= $dateString) {
                                        echo "deadline";
                                    } ?>" title="<?php echo "Due Date: " . $issue['fields']['duedate']; ?>"
                                         data-issue-key="<?= $issue['key'] ?>"
                                         data-date="<?= $issue['fields']['duedate'] ?>">
                                        <p>
                                            <?php if ($currentDate >= $dateString): ?>
                                            <svg width="14" height="14" fill="none" viewBox="0 0 16 16" role="presentation" class="svg-deadline"><path fill="currentcolor" fill-rule="evenodd" d="M5.7 1.383c.996-1.816 3.605-1.817 4.602-.002l5.35 9.73C16.612 12.86 15.346 15 13.35 15H2.667C.67 15-.594 12.862.365 11.113zm3.288.72a1.125 1.125 0 0 0-1.972.002L1.68 11.834c-.41.75.132 1.666.987 1.666H13.35c.855 0 1.398-.917.986-1.667z" clip-rule="evenodd"></path><path fill="currentcolor" fill-rule="evenodd" d="M7.25 9V4h1.5v5z" clip-rule="evenodd"></path><path fill="currentcolor" d="M9 11.25a1 1 0 1 1-2 0 1 1 0 0 1 2 0"></path></svg>
                                            <?php endif; ?>
                                            <svg width="16" height="16" class="svg-no-deadline" viewBox="0 0 24 24" role="presentation">
                                                <path d="M4.995 5h14.01C20.107 5 21 5.895 21 6.994v12.012A1.994 1.994 0 0119.005 21H4.995A1.995 1.995 0 013 19.006V6.994C3 5.893 3.892 5 4.995 5zM5 9v9a1 1 0 001 1h12a1 1 0 001-1V9H5zm1-5a1 1 0 012 0v1H6V4zm10 0a1 1 0 012 0v1h-2V4zm-9 9v-2.001h2V13H7zm8 0v-2.001h2V13h-2zm-4 0v-2.001h2.001V13H11zm-4 4v-2h2v2H7zm4 0v-2h2.001v2H11zm4 0v-2h2v2h-2z" fill="currentColor" fill-rule="evenodd"></path>
                                            </svg>
                                            <span><?php echo $formattedDate; ?></span>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="dueDate pointer-events-none" title=""
                                         data-issue-key="<?= $issue['key'] ?>"
                                         data-date="<?= $issue['fields']['duedate'] ?>">
                                    </div>
                                <?php endif; ?>

                                <div class="key-assignee">
                                    <p class="key"><img loading="lazy" decoding="async" src="https://dev-scvweb.atlassian.net/rest/api/2/universal_avatar/view/type/issuetype/avatar/10318?size=medium" alt=""><?php echo $issue["key"]; ?></p>
                                    <p class="icon-priority" data-issue-key="<?= $issue['key'] ?>" title="<?php echo $issue['fields']['priority']['name']; ?>"><img loading="lazy" decoding="async" src="<?php echo $issue['fields']['priority']['iconUrl']; ?>" alt=""></p>
                                    <?php if(isset($issue['fields']['assignee']) && $issue['fields']['assignee'] !== null):?>
                                        <p class="assignee"><img loading="lazy" decoding="async" title="<?php echo $issue['fields']['assignee']["displayName"]; ?>" src="<?php echo $issue['fields']['assignee']["avatarUrls"]["48x48"]; ?>" alt=""></p>
                                    <?php else: ?>
                                        <p class="assignee"><img loading="lazy" decoding="async" src="../../../assets/images/default-avatar.jpg"></p>
                                    <?php endif; ?>
                                </div>

                            </a>
                        </div>

                        <?php if ($childCount > 0): ?>
                            <div class="taskChild">

                                <?php $subtaskCount = 0; ?>

                                <div class="btn-taskChild-list">
                                    <div class="btn-box" title="Show subtasks">
                                        <svg width="24" height="24" viewBox="0 0 24 24" role="presentation"><g fill="currentColor"><path d="M19 7c1.105.003 2 .899 2 2.006v9.988A2.005 2.005 0 0118.994 21H9.006A2.005 2.005 0 017 19h11c.555 0 1-.448 1-1V7zM3 5.006C3 3.898 3.897 3 5.006 3h9.988C16.102 3 17 3.897 17 5.006v9.988A2.005 2.005 0 0114.994 17H5.006A2.005 2.005 0 013 14.994V5.006zM5 5v10h10V5H5z"></path><path d="M7.707 9.293a1 1 0 10-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 10-1.414-1.414L9 10.586 7.707 9.293z"></path></g></svg>
                                        <span class="count-task">
                                        <?php foreach ($childIssues as $childIssue) {
                                            if ($childIssue['fields']['status']['name'] == 'Done') {
                                                $subtaskCount++;
                                            }
                                        }
                                        ?>
                                            <?php echo $subtaskCount; ?>/<?php echo $childCount; ?></span>
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </div>
                                </div>

                                <div class="taskChildContent">

                                    <?php
                                    $countToDo = 0;
                                    $countInProgress = 0;
                                    $countDone = 0;
                                    $countVNCheck = 0;
                                    $countFix = 0;
                                    $countJPCheck = 0;
                                    $totalItems = count($childIssues);
                                    ?>
                                    <?php foreach ($childIssues as $childIssue){
                                        if ($childIssue['fields']['status']['name'] == 'Done'){
                                            $countDone++;
                                        } elseif ($childIssue['fields']['status']['name'] == 'In Progress'){
                                            $countInProgress++;
                                        } elseif ($childIssue['fields']['status']['name'] == 'To Do'){
                                            $countToDo++;
                                        } elseif ($childIssue['fields']['status']['name'] == 'VNCHECK'){
                                            $countVNCheck++;
                                        } elseif ($childIssue['fields']['status']['name'] == 'FIX'){
                                            $countFix++;
                                        } elseif ($childIssue['fields']['status']['name'] == 'JPCHECK'){
                                            $countJPCheck++;
                                        }
                                    }?>

                                    <div class="progressbar-border">
                                        <?php if ($countDone > 0): ?>
                                            <div class="progressbar" aria-valuemax="<?php echo $totalItems; ?>" style="flex-grow: <?php echo $countDone?>;" aria-valuenow="<?php echo $countDone?>">
                                                <div role="presentation">
                                                    <div class="_4t3ii2wt _bfhku8mo" aria-describedby="14074val-tooltip"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($countInProgress > 0): ?>
                                            <div class="progressbar" aria-valuemax="<?php echo $totalItems; ?>" style="flex-grow: <?php echo $countInProgress?>;" aria-valuenow="<?php echo $countInProgress?>">
                                                <div role="presentation">
                                                    <div class="_4t3ii2wt _bfhk9cbf" aria-describedby="14075val-tooltip"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($countToDo > 0): ?>
                                            <div class="progressbar" aria-valuemax="<?php echo $totalItems; ?>" style="flex-grow: <?php echo $countToDo?>;" aria-valuenow="<?php echo $countToDo?>">
                                                <div role="presentation">
                                                    <div class="_4t3ii2wt _bfhkhloo" aria-describedby="14074val-tooltip"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($countVNCheck > 0): ?>
                                            <div class="progressbar" aria-valuemax="<?php echo $totalItems; ?>" style="flex-grow: <?php echo $countVNCheck?>;" aria-valuenow="<?php echo $countVNCheck?>">
                                                <div role="presentation">
                                                    <div class="_4t3ii2wt _bfhkhloo" aria-describedby="14074val-tooltip"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($countFix > 0): ?>
                                            <div class="progressbar" aria-valuemax="<?php echo $totalItems; ?>" style="flex-grow: <?php echo $countFix?>;" aria-valuenow="<?php echo $countFix?>">
                                                <div role="presentation">
                                                    <div class="_4t3ii2wt _bfhkhloo" aria-describedby="14074val-tooltip"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($countJPCheck > 0): ?>
                                            <div class="progressbar" aria-valuemax="<?php echo $totalItems; ?>" style="flex-grow: <?php echo $countJPCheck?>;" aria-valuenow="<?php echo $countJPCheck?>">
                                                <div role="presentation">
                                                    <div class="_4t3ii2wt _bfhkhloo" aria-describedby="14074val-tooltip"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>


                                    </div>

                                    <div class="taskChild-list">
                                        <?php foreach ($childIssues as $child): ?>

                                            <div class="taskChild-item">
                                                <a class="open-task-child" data-issue-key="<?= $child['key'] ?>" href="#" data-id="<?= $child['id'] ?>">
                                                    <h3 class="title-taskChild summary" data-issue-key="<?= $child['key'] ?>"><?php echo $child['fields']['summary']; ?></h3>
                                                    <div class="key-assignee-taskChild">
                                                        <p class="key"><img loading="lazy" decoding="async" src="https://dev-scvweb.atlassian.net/rest/api/2/universal_avatar/view/type/issuetype/avatar/10316?size=medium" alt=""><?php echo $child["key"]; ?></p>
                                                        <div class="status-assignee">
                                                            <span class="status" style="background: #8fb8f6; color: #292a2e;"><?php echo $child['fields']["status"]["name"]; ?></span>
                                                            <?php if(isset($child['fields']['assignee']) && $child['fields']['assignee'] !== null):?>
                                                                <p class="assignee"><img loading="lazy" decoding="async" title="<?php echo $child['fields']['assignee']["displayName"]; ?>" src="<?php echo $child['fields']['assignee']["avatarUrls"]["48x48"]; ?>" alt=""></p>
                                                            <?php else: ?>
                                                                <p class="assignee"><img loading="lazy" decoding="async" src="../../../assets/images/default-avatar.jpg"></p>
                                                            <?php endif; ?>

                                                        </div>
                                                    </div>
                                                </a>

                                            </div>

                                        <?php endforeach; ?>
                                    </div>

                                </div>


                            </div>

                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

            <a href="/create-task" class="createTask" data-status="<?= $column['id'] ?>">
                <div class="btn-create"><i class="fa-solid fa-plus"></i><span>Create</span></div>
            </a>
        </div>

    </div>


    <?php endforeach; ?>

<div id="task-popup" class="taskContent-popup">
    <div class="popup-inner">
        <span class="close-popup"><i class="fa-solid fa-xmark"></i></span>
        <div id="task-popup-content" class="taskBox">

        </div>
    </div>
</div>

<script>
    window.initDashboardLabelSelects = function () {
        document.querySelectorAll('.allLabelsSelect').forEach(function (el) {
            if (el.tomselect) {
                el.tomselect.destroy();
            }

            new TomSelect(el, {
                plugins: ['remove_button'],
                persist: false,
                create: true,
                maxItems: null,
                placeholder: "Select labels..."
            });
        });
    };
</script>


<script>
    const editorInitialContent = {};

    tinymce.init({
        selector: '.tinymce-editor',
        height: 450,
        menubar: false,
        plugins: 'image advlist autolink lists link charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
        toolbar: 'bold italic backcolor | undo redo | formatselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image | help',
        branding: false,
        automatic_uploads: true,

        images_upload_handler: function (blobInfo) {

            return new Promise((resolve, reject) => {

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                fetch('/task/upload-image', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.location) {
                            resolve(data.location);
                        } else {
                            reject('Upload failed');
                        }
                    })
                    .catch(() => {
                        reject('Upload error');
                    });

            });
        },

        setup: function (editor) {
            let initialContent = '';

            editor.on('init', function () {
                editorInitialContent[editor.id] = editor.getContent();
            });

            editor.on('change keyup paste', function () {
                const currentContent = editor.getContent();
                const $textarea = $(editor.getElement());

                if (currentContent !== initialContent) {
                    $textarea
                        .closest('.taskContent-popup')
                        .find('.btn-update-description')
                        .removeClass('hidden');

                    initialContent = currentContent;
                }
            });
        }
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

<script>
    function initSortable() {

        let alertTimer = null;

        document.querySelectorAll('.boardContent-list')
            .forEach(function(column) {

                new Sortable(column, {
                    group: 'tasks',
                    animation: 250,
                    sort: false,

                    onEnd: function(evt) {

                        if (evt.from === evt.to) {
                            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                            return;
                        }

                        let issueKey = evt.item.dataset.issueKey;
                        let newStatusId = evt.to.dataset.statusId;

                        console.log(issueKey);
                        console.log(newStatusId);

                        $(".checkLoad").addClass("is-open");

                        fetch('/api/board/get-transitions?issueKey=' + issueKey)
                            .then(res => res.json())
                            .then(data => {



                                if (!data.transitions) return;

                                let transition = data.transitions.find(t =>
                                    String(t.to.id) === String(newStatusId)
                                );

                                if (!transition) {
                                    alert("No valid transition found!");
                                    evt.from.appendChild(evt.item);
                                    return;
                                }

                                $.ajax({
                                    url: '/api/board/move',
                                    method: 'POST',
                                    data: {
                                        issueKey: issueKey,
                                        transitionId: transition.id
                                    },
                                    success: function (res) {
                                        if (res.success) {

                                            $('.alert .info-alert').html('<p class="ttl">Update success</p>');
                                            $('.alert').addClass('is-open');

                                            if (alertTimer) {
                                                clearTimeout(alertTimer);
                                            }

                                            alertTimer = setTimeout(function () {
                                                $('.alert').removeClass('is-open');
                                            }, 2000);

                                        }
                                    }

                                });

                                $(".checkLoad").removeClass("is-open");

                            });
                    }
                });

            });
    }
</script>


<script>

    document.addEventListener("click", function (e) {
        const btnSave = e.target.closest(".btn-save");
        if (!btnSave) return;

        btnSave.classList.add("active");

        const wrapper = btnSave.closest(".btn-update-description");

        const container = wrapper.previousElementSibling;
        const textarea = container.querySelector(".tinymce-editor");

        if (!textarea) {
            console.error("Không tìm thấy textarea");
            return;
        }

        const issueId = textarea.id.replace("desc-", "");
        const editor = tinymce.get(textarea.id);

        if (!editor) {
            console.error("Không tìm thấy TinyMCE editor");
            return;
        }

        const htmlContent = editor.getContent();

        fetch("/task/update-description", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                issueId: issueId,
                description: htmlContent
            })
        })
            .then(res => res.text()) // debug
            .then(data => {
                console.log("Response:", data);
                wrapper.classList.add("hidden");
                btnSave.classList.remove("active");
            })
            .catch(err => {
                console.error("Fetch error:", err);
            });
    });

</script>
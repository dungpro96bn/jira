<?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="main">
        <div class="inner">
            <div id="createTask">
                <h2 class="heading-main">Create Tasks On Jira</h2>

                <div class="form-createTask">
                    <form id="formCreateTask" action="/create-task" method="POST">
                        <div class="createTask-list">

                            <!-- Title -->
                            <div class="field-group">
                                <label class="title">Title<span>*</span></label>
                                <div class="field-input">
                                    <input type="text" name="summary" id="summary" required>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="field-group">
                                <label class="title">Description</label>
                                <div class="field-input">
                                    <div class="input-rich"></div>
                                    <textarea class="input-raw hidden"></textarea>
                                    <textarea class="hidden" name="description" id="description"></textarea>
                                </div>
                            </div>

                            <div class="field-group attachments">
                                <label class="title">Attachments</label>
                                <div class="field-input">
                                    <input type="file" name="attachments[]" multiple>
                                </div>
                            </div>

                            <!-- Assignee -->
                            <div class="field-group">
                                <label class="title">Assignee</label>
                                <div class="field-input">
                                    <div class="user-active">
                                        <div class="user-item-active" data-id="">
                                            <div class="avt">
                                            <span class="icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24">
                                                    <g fill="#fff">
                                                        <path d="M6 14c0-1.105.902-2 2.009-2h7.982c1.11 0 2.009.894 2.009 2.006v4.44c0 3.405-12 3.405-12 0V14z"></path>
                                                        <circle cx="12" cy="7" r="4"></circle>
                                                    </g>
                                                </svg>
                                            </span>
                                            </div>
                                            <span>Assignee</span>
                                        </div>
                                    </div>

                                    <div class="user-list">
                                        <div class="user-item item01" data-id="">
                                            <div class="avt">
                                            <span class="icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24">
                                                    <g fill="#fff">
                                                        <path d="M6 14c0-1.105.902-2 2.009-2h7.982c1.11 0 2.009.894 2.009 2.006v4.44c0 3.405-12 3.405-12 0V14z"></path>
                                                        <circle cx="12" cy="7" r="4"></circle>
                                                    </g>
                                                </svg>
                                            </span>
                                            </div>
                                            <span>Unassigned</span>
                                        </div>

                                        <?php if (!empty($users)) : ?>
                                            <?php foreach ($users as $user) : ?>
                                                <div class="user-item" data-id="<?= htmlspecialchars($user['accountId']) ?>">
                                                    <img loading="lazy" decoding="async" src="<?= htmlspecialchars($user['avatarUrls']['48x48']) ?>">
                                                    <span><?= htmlspecialchars($user['displayName']) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <input type="hidden" id="assignee" name="assignee" value="">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="title">Labels</label>
                                <div class="field-input">
                                    <select id="labelSelect" name="labels[]" hidden="hidden" multiple>
                                        <?php foreach ($labels as $label): ?>
                                            <option value="<?= $label ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group priority">
                                <label class="title">Priority</label>
                                <div class="field-input">
                                    <select id="prioritySelect" name="priority" hidden="hidden">
                                        <option value="">Select priority</option>
                                        <?php foreach ($priorities as $priority): ?>
                                            <option value="<?= $priority['id'] ?>"
                                                    data-icon-url="<?= $priority['iconUrl'] ?>">
                                                <?= $priority['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                            <!-- Due date -->
                            <div class="field-group">
                                <label class="title">Due date</label>
                                <div class="field-input">
                                    <input type="date" id="duedate" name="duedate">
                                </div>
                            </div>

                        </div>

                        <div class="submit-form">
                            <div class="btn-inner">
                                <input type="submit" id="submitBtn" value="Create Task">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="60" height="60" style="opacity:0;">
                                    <circle cx="50" cy="50" fill="none" stroke="#ffffff" stroke-width="10" r="40" stroke-dasharray="188.495 64.831">
                                        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50"/>
                                    </circle>
                                </svg>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Modal -->
                <div class="swal-overlay">
                    <div class="swal-mask"></div>
                    <div class="swal-modal">
                        <div class="boxClose">X</div>
                        <div id="response" class="note-task"></div>
                    </div>
                </div>

            </div>
        </div>
    </main>

<script>
    new TomSelect("#labelSelect", {
        plugins: ['remove_button'],
        persist: false,
        create: true,
        maxItems: null,
        placeholder: "Select labels..."
    });

    new TomSelect("#prioritySelect", {
        maxItems: 1,
        render: {
            option: function(data, escape) {
                return `
                <div>
                    <img src="${data.iconUrl}" width="16" style="margin-right:6px;">
                    ${escape(data.text)}
                </div>
            `;
            },
            item: function(data, escape) {
                return `
                <div>
                    <img src="${data.iconUrl}" width="16" style="margin-right:6px;">
                    ${escape(data.text)}
                </div>
            `;
            }
        }
    });
</script>

    <script>
        $(document).ready(function() {

            $('#formCreateTask').submit(function(event) {
                event.preventDefault();

                const editor = tinymce.activeEditor;

                if (editor) {
                    const content = editor.getContent();
                    $('#description').val(content);
                    // console.log("HTML gửi:", content);
                } else {
                    console.error("TinyMCE chưa init");
                }

                const formData = new FormData(this);

                $(".submit-form svg").css("opacity", "1");

                $.ajax({
                    type: 'POST',
                    url: '/create-task',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {

                        $(".submit-form svg").css("opacity", "0");

                        if (response.success) {

                            $('#response').html(
                                '<strong>Task created successfully!</strong><br>' +
                                'Issue Key: ' + response.key + '<br/>' +
                                '<a target="_blank" href="https://dev-scvweb.atlassian.net/jira/core/projects/JIRA2024/board?groupBy=status&selectedIssue='+ response.key +'">Link Task</a>'
                            );

                        } else {

                            $('#response').html(
                                '<strong>Error:</strong><br>' +
                                response.message
                            );

                        }

                        $(".swal-overlay").addClass("swal-overlay--show-modal");

                        $('#formCreateTask')[0].reset();
                        $(".user-item-active").html($(".user-item.item01").html());
                    },
                    error: function(xhr) {

                        $(".submit-form svg").css("opacity", "0");

                        $('#response').html(
                            '<strong>Server Error</strong><br>' +
                            xhr.responseText
                        );

                        $(".swal-overlay").addClass("swal-overlay--show-modal");
                    }
                });
            });

            $(".swal-mask, .boxClose").click(function () {
                $(".swal-overlay").removeClass("swal-overlay--show-modal");
            });

        });
    </script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
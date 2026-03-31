
<div class="left-taskInfo">
    <p class="key"><img loading="lazy" decoding="async" src="https://dev-scvweb.atlassian.net/rest/api/2/universal_avatar/view/type/issuetype/avatar/10316?size=medium" alt=""><?php echo $task["key"]; ?></p>
    <div class="main-scroll">
        <h4 class="board-summary summary">
            <input
                    type="text"
                    class="input-summary"
                    data-issue-key="<?= $task['key'] ?>"
                    data-original="<?= htmlspecialchars($task['fields']['summary']) ?>"
                    value="<?= htmlspecialchars($task['fields']['summary']) ?>">
        </h4>
        <div class="taskInfo-item description">
            <p class="info-title">Description</p>
            <div class="main-description input-rich">
                <textarea class="tinymce-editor" id="desc-<?= $task['id'] ?>">
                    <?php
                    $description = $task['fields']['description'] ?? '';
                    $attachments = $task['fields']['attachment'] ?? [];

                    if (is_array($description)) {
                        echo renderADF($description, $attachments);
                    } else {
                        echo $description;
                    }
                    ?>
                </textarea>
            </div>
            <div class="btn-update-description hidden">
                <div class="btn-update">
                    <button class="btn-save">Save</button>
                    <button class="btn-cancel">Cancel</button>
                </div>
            </div>
        </div>

        <div class="attachments-block">
            <?php
            $attachments = $task['fields']['attachment'] ?? [];
            $countAttachments = count($attachments);
            ?>
            <p class="title-attachments info-title">Attachments <span class="count-attachments"><?= $countAttachments ?></span></p>

            <div class="attachments-list">
                <?php if (!empty($attachments)) : ?>

                    <?php foreach ($attachments as $att) :

                        $id   = $att['id'] ?? '';
                        $name = htmlspecialchars($att['filename'] ?? '', ENT_QUOTES, 'UTF-8');
                        $type = $att['mimeType'] ?? '';

                        $isImage = str_starts_with($type, 'image');
                        // $isZip   = str_contains($type, 'zip');
                        // $isPdf   = str_contains($type, 'pdf');

                        $proxyUrl = "/attachment-proxy?id=" . $id;
                        ?>

                        <div class="attachment <?= $isImage ? 'image' : 'file' ?>">

                            <a href="<?= $proxyUrl ?>" target="_blank" class="attachment-link">

                                <div class="image iconWrapper">
                                    <?php if ($isImage): ?>
                                        <img loading="lazy" decoding="async" src="<?= $proxyUrl ?>" alt="<?= $name ?>">
                                    <?php else: ?>
                                        <span data-testid="media-card-file-type-icon" data-type="archive" class="_1e0c116y _ca0q1b66 _u5f31b66 _n3td1b66 _19bv1b66"><span data-vc="icon-undefined" role="img" aria-label="media-type" style="--icon-primary-color: currentColor;" class="_1e0c1o8l _1o9zidpf _vyfuvuon _vwz4kb7n _1szv15vq _1tly15vq _rzyw1osq _17jb1osq _1ksvoz0e _3se1x1jp _re2rglyw _1veoyfq0 _1kg81r31 _jcxd1r8n _gq0g1onz _1trkwc43 _1bsb1tcg _4t3i1tcg _5fdi1tcg _zbji1tcg"><svg width="24" height="24" viewBox="0 0 24 24" role="presentation"><path fill="#758195" fill-rule="evenodd" d="M3 0h18a3 3 0 0 1 3 3v18a3 3 0 0 1-3 3H3a3 3 0 0 1-3-3V3a3 3 0 0 1 3-3m6 3v3h3V3zm3 3v3h3V6zM9 9v3h3V9zm3 3v3h3v-3zm-3 3v3h3v-3zm3 3v3h3v-3z"></path></svg></span></span>
                                    <?php endif; ?>
                                </div>

                                <p class="attachment-name"><?= $name ?></p>
                            </a>

                            <a class="download-attachment" href="/attachment-proxy?id=<?= $att['id'] ?>&name=<?= urlencode($att['filename']) ?>" download>
                                <svg fill="none" viewBox="-4 -4 24 24">
                                    <path fill="currentcolor" fill-rule="evenodd"
                                          d="M8.75 1v7.44l2.72-2.72 1.06 1.06-4 4a.75.75 0 0 1-1.06 0l-4-4 1.06-1.06 2.72 2.72V1zM1 13V9h1.5v4a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V9H15v4a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2">
                                    </path>
                                </svg>
                            </a>

                        </div>

                    <?php endforeach; ?>

                <?php else : ?>
                    <p>No attachments</p>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>
<?php
$issueId = $task['id'];
?>
<div class="right-taskInfo" data-issue-key="<?= $task['key'] ?>" data-task-id="<?= $issueId ?>">
    <p class="status" style="background: #669df1;color: #292a2e;"><?php echo $task['fields']['status']['name'];?></p>
    <div class="details">
        <h4 class="d-title">details</h4>
        <div class="details-item">
            <div class="title">assignee</div>
            <div class="item-info assignee-item">
                <div class="assignee-wrapper">
                    <?php if(isset($task['fields']['assignee']) && $task['fields']['assignee'] !== null):?>
                        <div class="user-option assignee-selected">
                            <span class="icon"><img loading="lazy" decoding="async" src="<?php echo $task['fields']['assignee']["avatarUrls"]["48x48"]; ?>" alt=""></span>
                            <span class="name"><?php echo $task['fields']['assignee']["displayName"]; ?></span>
                        </div>
                    <?php else:?>
                        <div class="user-option assignee-selected" data-id="">
                            <span class="icon"><img loading="lazy" decoding="async" src="../../../assets/images/default-avatar.jpg"></span>
                            <span class="name">Unassigned</span>
                        </div>
                    <?php endif; ?>

                    <div class="assignee-dropdown hidden">
                        <?php if(isset($task['fields']['assignee']) && $task['fields']['assignee'] !== null):?>
                            <div class="user-option" data-id="">
                                <span class="icon"><img loading="lazy" decoding="async" src="../../../assets/images/default-avatar.jpg"></span>
                                <span class="name">Unassigned</span>
                            </div>
                        <?php endif; ?>
                        <?php foreach ($users as $user) : ?>
                            <div class="user-option"
                                 data-id="<?= $user['accountId'] ?>">
                                <span class="icon"><img loading="lazy" decoding="async" src="<?= $user['avatarUrls']['48x48'] ?>"></span>
                                <span class="name"><?= $user['displayName'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="details-item">
            <div class="title">priority</div>

            <div class="priority-wrapper priority-item item-info">

                <!-- current -->
                <div class="priority-selected">
                    <span class="icon">
                        <img src="<?= $task['fields']['priority']['iconUrl'] ?>">
                    </span>
                    <span class="priority-name">
                        <?= $task['fields']['priority']['name'] ?>
                    </span>
                </div>

                <!-- dropdown -->
                <div class="priority-dropdown hidden">

                    <?php
                    $priorities = [
                        ['name' => 'Highest', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/highest_new.svg'],
                        ['name' => 'High', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/high_new.svg'],
                        ['name' => 'Medium', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/medium_new.svg'],
                        ['name' => 'Low', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/low_new.svg'],
                        ['name' => 'Lowest', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/lowest_new.svg'],
                    ];
                    ?>

                    <?php foreach ($priorities as $p): ?>
                        <div class="priority-option" data-name="<?= $p['name'] ?>">
                            <span class="icon">
                                <img src="<?= $p['icon'] ?>">
                            </span>
                            <span class="name"><?= $p['name'] ?></span>
                        </div>
                    <?php endforeach; ?>

                </div>

            </div>
        </div>

        <div class="details-item labels">
            <div class="title">Labels</div>
            <?php $labels = $task['fields']['labels'];
            if($labels) :?>
                <div class="label-list item-info">
                    <?php foreach ($labels as $label) :?>
                        <span class="label-item"><?= $label ?></span>
                    <?php endforeach;?>
                </div>
            <?php endif;?>
        </div>
        <div class="due-date details-item">
            <div class="title">due date</div>
            <?php if (isset($task['fields']['duedate'])): ?>
                <?php
                $currentDate = date('Y-m-d');
                $dateString = $task['fields']['duedate'];
                $date = date_create_from_format('Y-m-d', $dateString);
                $formattedDate = date_format($date, 'F j, Y');
                ?>
                <div class="dueDate dueDate-item item-info <?php if ($currentDate >= $dateString) {
                    echo "deadline";
                } ?>" title="<?php echo "Due Date: " . $task['fields']['duedate']; ?>"
                     data-issue-key="<?= $task['key'] ?>"
                     data-date="<?= $task['fields']['duedate'] ?>">
                    <p>
                        <svg width="16" height="16" viewBox="0 0 24 24" role="presentation">
                            <path d="M4.995 5h14.01C20.107 5 21 5.895 21 6.994v12.012A1.994 1.994 0 0119.005 21H4.995A1.995 1.995 0 013 19.006V6.994C3 5.893 3.892 5 4.995 5zM5 9v9a1 1 0 001 1h12a1 1 0 001-1V9H5zm1-5a1 1 0 012 0v1H6V4zm10 0a1 1 0 012 0v1h-2V4zm-9 9v-2.001h2V13H7zm8 0v-2.001h2V13h-2zm-4 0v-2.001h2.001V13H11zm-4 4v-2h2v2H7zm4 0v-2h2.001v2H11zm4 0v-2h2v2h-2z" fill="currentColor" fill-rule="evenodd"></path>
                        </svg>
                        <span><?= $formattedDate ?></span>
                    </p>
                    <input type="date" class="dueDate-input" value="<?= $task['fields']['duedate'] ?>">
                </div>
            <?php else: ?>
                <div class="dueDate dueDate-item item-info" title="<?php echo "Due Date: " . $task['fields']['duedate']; ?>"
                     data-issue-key="<?= $task['key'] ?>"
                     data-date="<?= $task['fields']['duedate'] ?>">
                    <p>
                        <svg width="16" height="16" viewBox="0 0 24 24" role="presentation">
                            <path d="M4.995 5h14.01C20.107 5 21 5.895 21 6.994v12.012A1.994 1.994 0 0119.005 21H4.995A1.995 1.995 0 013 19.006V6.994C3 5.893 3.892 5 4.995 5zM5 9v9a1 1 0 001 1h12a1 1 0 001-1V9H5zm1-5a1 1 0 012 0v1H6V4zm10 0a1 1 0 012 0v1h-2V4zm-9 9v-2.001h2V13H7zm8 0v-2.001h2V13h-2zm-4 0v-2.001h2.001V13H11zm-4 4v-2h2v2H7zm4 0v-2h2.001v2H11zm4 0v-2h2v2h-2z" fill="currentColor" fill-rule="evenodd"></path>
                        </svg>
                        <span></span>
                    </p>
                    <input type="date" class="dueDate-input" value="<?= $task['fields']['duedate'] ?>">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$issueKey = $task['key'];
$issueId = $task['id'];
$fields = $task['fields'] ?? [];
$attachments = $fields['attachment'] ?? [];
$labels = $fields['labels'] ?? [];
$subtasks = $fields['subtasks'] ?? [];
$comments = $fields['comment']['comments'] ?? [];
$worklogs = $fields['worklog']['worklogs'] ?? [];
$histories = $task['changelog']['histories'] ?? [];
$timeTracking = $fields['timetracking'] ?? [];
$timeSpentSeconds = (int)($timeTracking['timeSpentSeconds'] ?? 0);
$remainingSeconds = (int)($timeTracking['remainingEstimateSeconds'] ?? 0);
$totalTracked = $timeSpentSeconds + $remainingSeconds;
$timeProgress = $totalTracked > 0 ? round(($timeSpentSeconds / $totalTracked) * 100) : 0;

function popupFormatDate($dateString, $format = 'F j, Y')
{
    if (!$dateString) return '';
    try {
        return (new DateTime($dateString))->format($format);
    } catch (Exception $e) {
        return $dateString;
    }
}

function popupRelativeTime($dateString)
{
    if (!$dateString) return '';
    try {
        $date = new DateTime($dateString);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $date->getTimestamp();
        if ($diff < 60) return $diff . ' seconds ago';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        return floor($diff / 86400) . ' days ago';
    } catch (Exception $e) {
        return '';
    }
}

function popupRenderAdfText($content, $attachments = [])
{
    if (is_array($content)) {
        return renderADF($content, $attachments);
    }
    return nl2br(htmlspecialchars((string)$content));
}

function popupUserAvatar($user)
{
    return $user['avatarUrls']['48x48'] ?? '/assets/images/default-avatar.jpg';
}

function popupPriorityIcon($priority)
{
    if (!$priority || !is_array($priority)) return '';
    return $priority['iconUrl'] ?? '';
}

function popupStatusClass($status)
{
    $map = [
        'To Do' => 'todo',
        'In Progress' => 'progress',
        'Done' => 'done',
        'Delivered' => 'done',
        'Pending' => 'pending',
        'JPCheck' => 'pending',
        'VNCheck' => 'pending',
        'Fix' => 'progress',
    ];

    return $map[$status] ?? 'default';
}

?>

<div class="left-taskInfo">
    <p class="key"><img loading="lazy" decoding="async"
                        src="https://dev-scvweb.atlassian.net/rest/api/2/universal_avatar/view/type/issuetype/avatar/10316?size=medium"
                        alt=""><?php echo htmlspecialchars($issueKey); ?></p>
    <div class="main-scroll">
        <h4 class="board-summary summary">
            <input
                type="text"
                class="input-summary"
                data-issue-key="<?= htmlspecialchars($issueKey) ?>"
                data-original="<?= htmlspecialchars($fields['summary'] ?? '') ?>"
                value="<?= htmlspecialchars($fields['summary'] ?? '') ?>">
        </h4>

        <div class="taskInfo-item description">
            <p class="info-title">Description</p>
            <div class="main-description input-rich">
                <textarea class="tinymce-editor" id="desc-<?= htmlspecialchars($issueId) ?>"><?php
                    $description = $fields['description'] ?? '';
                    if (is_array($description)) {
                        echo renderADF($description, $attachments);
                    } else {
                        echo htmlspecialchars((string)$description);
                    }
                    ?></textarea>
            </div>
            <div class="btn-update-description hidden">
                <div class="btn-update">
                    <button class="btn-save">Save</button>
                    <button class="btn-cancel">Cancel</button>
                </div>
            </div>
        </div>

        <div class="attachments-block">
            <p class="title-attachments info-title">Attachments <span
                    class="count-attachments"><?= count($attachments) ?></span></p>
            <div class="attachments-list">
                <?php if (!empty($attachments)) : ?>
                    <?php foreach ($attachments as $att) :
                        $id = $att['id'] ?? '';
                        $name = htmlspecialchars($att['filename'] ?? '', ENT_QUOTES, 'UTF-8');
                        $type = $att['mimeType'] ?? '';
                        $isImage = strpos($type, 'image') === 0;
                        $proxyUrl = '/attachment-proxy?id=' . $id;
                        ?>
                        <div class="attachment <?= $isImage ? 'image' : 'file' ?>">
                            <a href="<?= $proxyUrl ?>" target="_blank" class="attachment-link">
                                <div class="image iconWrapper">
                                    <?php if ($isImage): ?>
                                        <img loading="lazy" decoding="async" src="<?= $proxyUrl ?>" alt="<?= $name ?>">
                                    <?php else: ?>
                                        <span class="attachment-file-icon">📎</span>
                                    <?php endif; ?>
                                </div>
                                <p class="attachment-name"><?= $name ?></p>
                            </a>
                            <a class="download-attachment"
                               href="/attachment-proxy?id=<?= $id ?>&name=<?= urlencode($att['filename']) ?>"
                               download>⬇</a>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="empty-note">No attachments</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="taskInfo-item subtasks-block">
            <div class="section-head-row">
                <div>
                    <p class="info-title">Subtasks</p>
                    <?php if (empty($subtasks)): ?>
                        <p class="empty-note">No subtasks yet.</p>
                    <?php endif; ?>
                </div>
                <?php if (empty($fields['parent'])): ?>
                    <button type="button" class="toggle-subtask-form">+ Add subtask</button>
                <?php endif; ?>
            </div>

            <div class="subtasks-list">
                <?php if (!empty($subtasks)): ?>
                    <?php foreach ($subtasks as $subtask):
                        $subFields = $subtask['fields'] ?? [];
                        $subAssignee = $subFields['assignee'] ?? null;
                        $subStatus = $subFields['status']['name'] ?? 'To Do';
                        $subPriority = $subFields['priority'] ?? null;
                        ?>
                        <div class="subtask-row open-task-child" data-id="<?= htmlspecialchars($subtask['id'] ?? '') ?>"
                             data-issue-key="<?= htmlspecialchars($subtask['key'] ?? '') ?>">
                            <div class="subtask-work">
                                <strong>
                                    <img height="16" width="16"
                                         src="https://dev-scvweb.atlassian.net/rest/api/2/universal_avatar/view/type/issuetype/avatar/10316?size=small"
                                         alt="Sub-task" data-vc="native-issue-table-ui-icon-cell-img">
                                    <?= htmlspecialchars($subtask['key'] ?? '') ?>
                                </strong>
                                <span><?= htmlspecialchars($subFields['summary'] ?? '') ?></span>
                            </div>
                            <div class="subtask-meta priority">
                                <?php if (!empty($subPriority['iconUrl'])): ?>
                                    <img loading="lazy" decoding="async"
                                         src="<?= htmlspecialchars(popupPriorityIcon($subPriority)) ?>" alt="">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($subPriority['name'] ?? '—') ?></span>
                            </div>
                            <div class="subtask-meta assignee">
                                <?php if ($subAssignee): ?>
                                    <img loading="lazy" decoding="async"
                                         src="<?= htmlspecialchars(popupUserAvatar($subAssignee)) ?>" alt="">
                                    <span><?= htmlspecialchars($subAssignee['displayName']) ?></span>
                                <?php else: ?>
                                    <img loading="lazy" decoding="async" src="/assets/images/default-avatar.jpg" alt="">
                                    <span>Unassigned</span>
                                <?php endif; ?>
                            </div>
                            <div class="subtask-meta status">
                                <div class="popup-status-wrapper">
                                    <button type="button"
                                            class="popup-status-trigger subtask-status-trigger <?= htmlspecialchars(popupStatusClass($subStatus)) ?>"
                                            data-issue-key="<?= htmlspecialchars($subtask['key'] ?? '') ?>"
                                            data-task-id="<?= htmlspecialchars($subtask['id'] ?? '') ?>">
                                        <span class="popup-status-label"><?= htmlspecialchars($subStatus) ?></span>
                                        <span class="popup-status-caret"><i class="fa-solid fa-chevron-down"></i></span>
                                    </button>
                                    <div class="popup-status-menu hidden"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (empty($fields['parent'])): ?>
                <form class="subtask-create-form hidden" data-parent-key="<?= htmlspecialchars($issueKey) ?>">
                    <input type="text" name="summary" placeholder="What needs to be done?" required>
                    <div class="subtask-form-actions">
                        <button type="submit" class="subtask-submit">Create subtask</button>
                        <button type="button" class="subtask-cancel">Cancel</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="taskInfo-item activity-block">
            <p class="info-title">Activity</p>
            <div class="activity-tabs">
                <button type="button" class="activity-tab is-active" data-pane="comments">Comments</button>
                <button type="button" class="activity-tab" data-pane="worklog">Work log</button>
                <button type="button" class="activity-tab" data-pane="history">History</button>
            </div>

            <div class="activity-pane is-active" data-pane="comments">
                <form class="task-comment-form" data-issue-key="<?= htmlspecialchars($issueKey) ?>">
                    <textarea name="comment" placeholder="Add a comment..." required></textarea>
                    <div class="activity-form-actions">
                        <button type="submit">Comment</button>
                    </div>
                </form>
                <div class="activity-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): $author = $comment['author'] ?? null; ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <img loading="lazy" decoding="async"
                                         src="<?= htmlspecialchars($author ? popupUserAvatar($author) : '/assets/images/default-avatar.jpg') ?>"
                                         alt="">
                                </div>
                                <div class="activity-content">
                                    <div class="activity-meta">
                                        <strong><?= htmlspecialchars($author['displayName'] ?? 'Unknown') ?></strong>
                                        <span><?= htmlspecialchars(popupRelativeTime($comment['created'] ?? '')) ?></span>
                                    </div>
                                    <div
                                        class="activity-body"><?= popupRenderAdfText($comment['body'] ?? '', $attachments) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-note">No comments yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="activity-pane" data-pane="worklog">
                <div class="activity-list">
                    <?php if (!empty($worklogs)): ?>
                        <?php foreach ($worklogs as $worklog): $author = $worklog['author'] ?? null; ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <img loading="lazy" decoding="async"
                                         src="<?= htmlspecialchars($author ? popupUserAvatar($author) : '/assets/images/default-avatar.jpg') ?>"
                                         alt="">
                                </div>
                                <div class="activity-content">
                                    <div class="activity-meta">
                                        <strong><?= htmlspecialchars($author['displayName'] ?? 'Unknown') ?></strong>
                                        <span><?= htmlspecialchars($worklog['timeSpent'] ?? '') ?> · <?= htmlspecialchars(popupRelativeTime($worklog['created'] ?? '')) ?></span>
                                    </div>
                                    <div
                                        class="activity-body"><?= popupRenderAdfText($worklog['comment'] ?? '', $attachments) ?: '<p>No comment</p>' ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-note">No work logs yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="activity-pane" data-pane="history">
                <div class="activity-list">
                    <?php if (!empty($histories)): ?>
                        <?php foreach ($histories as $history): $author = $history['author'] ?? null; ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <img loading="lazy" decoding="async"
                                         src="<?= htmlspecialchars($author ? popupUserAvatar($author) : '/assets/images/default-avatar.jpg') ?>"
                                         alt="">
                                </div>
                                <div class="activity-content">
                                    <div class="activity-meta">
                                        <strong><?= htmlspecialchars($author['displayName'] ?? 'Unknown') ?></strong>
                                        <span><?= htmlspecialchars(popupRelativeTime($history['created'] ?? '')) ?></span>
                                    </div>
                                    <div class="activity-body">
                                        <ul class="history-changes">
                                            <?php foreach (($history['items'] ?? []) as $item): ?>
                                                <li>
                                                    <strong><?= htmlspecialchars($item['field'] ?? '') ?>:</strong>
                                                    <?= htmlspecialchars($item['fromString'] ?? 'empty') ?>
                                                    → <?= htmlspecialchars($item['toString'] ?? 'empty') ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-note">No history available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="right-taskInfo" data-issue-key="<?= htmlspecialchars($issueKey) ?>"
     data-task-id="<?= htmlspecialchars($issueId) ?>">
    <?php $currentStatus = $fields['status']['name'] ?? ""; ?>
    <div class="popup-status-wrapper task-status-wrapper">
        <button type="button"
                class="popup-status-trigger task-status-trigger <?= htmlspecialchars(popupStatusClass($currentStatus)) ?>"
                data-issue-key="<?= htmlspecialchars($issueKey) ?>" data-task-id="<?= htmlspecialchars($issueId) ?>">
            <span class="popup-status-label"><?= htmlspecialchars($currentStatus) ?></span>
            <span class="popup-status-caret"><i class="fa-solid fa-chevron-down"></i></span>
        </button>
        <div class="popup-status-menu hidden"></div>
    </div>
    <div class="details">
        <h4 class="d-title">details</h4>
        <div class="details-item">
            <div class="title">assignee</div>
            <div class="item-info assignee-item">
                <div class="assignee-wrapper">
                    <?php if (isset($fields['assignee']) && $fields['assignee'] !== null): ?>
                        <div class="user-option assignee-selected">
                            <span class="icon"><img loading="lazy" decoding="async"
                                                    src="<?php echo htmlspecialchars($fields['assignee']['avatarUrls']['48x48']); ?>"
                                                    alt=""></span>
                            <span
                                class="name"><?php echo htmlspecialchars($fields['assignee']['displayName']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="user-option assignee-selected" data-id="">
                            <span class="icon"><img loading="lazy" decoding="async"
                                                    src="/assets/images/default-avatar.jpg" alt=""></span>
                            <span class="name">Unassigned</span>
                        </div>
                    <?php endif; ?>

                    <div class="assignee-dropdown hidden">
                        <?php if (isset($fields['assignee']) && $fields['assignee'] !== null): ?>
                            <div class="user-option" data-id="">
                                <span class="icon"><img loading="lazy" decoding="async"
                                                        src="/assets/images/default-avatar.jpg" alt=""></span>
                                <span class="name">Unassigned</span>
                            </div>
                        <?php endif; ?>
                        <?php foreach ($users as $user) : ?>
                            <div class="user-option" data-id="<?= htmlspecialchars($user['accountId']) ?>">
                                <span class="icon"><img loading="lazy" decoding="async"
                                                        src="<?= htmlspecialchars($user['avatarUrls']['48x48']) ?>"
                                                        alt=""></span>
                                <span class="name"><?= htmlspecialchars($user['displayName']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="details-item">
            <div class="title">priority</div>
            <div class="priority-wrapper priority-item item-info">
                <div class="priority-selected">
                    <span class="icon"><img src="<?= htmlspecialchars($fields['priority']['iconUrl'] ?? '') ?>" alt=""></span>
                    <span class="priority-name"><?= htmlspecialchars($fields['priority']['name'] ?? 'None') ?></span>
                </div>
                <div class="priority-dropdown hidden">
                    <?php $priorities = [
                        ['name' => 'Highest', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/highest_new.svg'],
                        ['name' => 'High', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/high_new.svg'],
                        ['name' => 'Medium', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/medium_new.svg'],
                        ['name' => 'Low', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/low_new.svg'],
                        ['name' => 'Lowest', 'icon' => 'https://dev-scvweb.atlassian.net/images/icons/priorities/lowest_new.svg'],
                    ]; ?>
                    <?php foreach ($priorities as $p): ?>
                        <div class="priority-option" data-name="<?= htmlspecialchars($p['name']) ?>">
                            <span class="icon"><img src="<?= htmlspecialchars($p['icon']) ?>" alt=""></span>
                            <span class="name"><?= htmlspecialchars($p['name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="details-item labels">
            <div class="title">Labels</div>
            <div class="label-view label-list item-info">
                <?php if (!empty($labels)): ?>
                    <?php foreach ($labels as $label): ?>
                        <span class="label-item"><?= htmlspecialchars($label) ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="label-empty">None</span>
                <?php endif; ?>
            </div>
            <div class="label-edit hidden item-info">
                <select class="allLabelsSelect" multiple data-issue-key="<?= htmlspecialchars($issueKey) ?>">
                    <?php foreach ($allLabels as $label): ?>
                        <option value="<?= htmlspecialchars($label) ?>"
                                <?= in_array($label, $labels, true) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                    <?php foreach ($labels as $label): ?>
                        <?php if (!in_array($label, $allLabels, true)): ?>
                            <option value="<?= htmlspecialchars($label) ?>"
                                    selected><?= htmlspecialchars($label) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="details-item time-tracking-item">
            <div class="title">time tracking</div>
            <div class="item-info time-tracking-summary">
                <?php if (!empty($timeTracking['timeSpent']) || !empty($timeTracking['remainingEstimate']) || !empty($timeTracking['originalEstimate'])) : ?>
                    <div class="time-tracking-values">
                        <?php if (!empty($timeTracking['timeSpent'])) : ?>
                            <div class="time-track-row">
                                <strong><?= htmlspecialchars($timeTracking['timeSpent']) ?> logged</strong>
                            </div>
                        <?php endif; ?>

                        <!--                    --><?php //if (!empty($timeTracking['remainingEstimate'])) : ?>
                        <!--                        <div class="time-track-row">-->
                        <!--                            <span>Remaining</span>-->
                        <!--                            <strong>-->
                        <? //= htmlspecialchars($timeTracking['remainingEstimate']) ?><!--</strong>-->
                        <!--                        </div>-->
                        <!--                    --><?php //endif; ?>
                        <!---->
                        <!--                    --><?php //if (!empty($timeTracking['originalEstimate'])) : ?>
                        <!--                        <div class="time-track-row">-->
                        <!--                            <span>Original</span>-->
                        <!--                            <strong>-->
                        <? //= htmlspecialchars($timeTracking['originalEstimate']) ?><!--</strong>-->
                        <!--                        </div>-->
                        <!--                    --><?php //endif; ?>
                    </div>
                <?php endif; ?>
                <div class="jira-time-progress"><span style="width: <?= $timeProgress ?>%"></span></div>
                <?php if (!empty($timeTracking['timeSpent'])) : ?>
                    <button type="button" class="open-time-tracking log-time">Log time</button>
                <?php else: ?>
                    <button type="button" class="open-time-tracking no-time">No time logged</button>
                <?php endif; ?>
            </div>
            <div class="time-tracking-modal">
                <div class="time-tracking-dialog">
                    <button type="button" class="time-tracking-close"><i class="fa-solid fa-xmark"></i></button>
                    <h3>Time tracking</h3>
                    <form class="time-tracking-form" data-issue-key="<?= htmlspecialchars($issueKey) ?>">
                        <div class="time-tracking-grid">
                            <div class="field">
                                <label>Time spent</label>
                                <input type="text" name="timeSpent" placeholder="2w 4d 6h 45m" required>
                            </div>
                            <div class="field">
                                <label>Time remaining</label>
                                <input type="text" name="remainingEstimate" placeholder="1w 2d 3h">
                            </div>
                        </div>
                        <div class="field">
                            <label>Comment</label>
                            <textarea name="comment" rows="4" placeholder="What did you work on?"></textarea>
                        </div>
                        <div class="time-tracking-help">
                            <p>Use the format: 2w 4d 6h 45m</p>
                            <ul>
                                <li>w = weeks</li>
                                <li>d = days</li>
                                <li>h = hours</li>
                                <li>m = minutes</li>
                            </ul>
                        </div>
                        <div class="time-tracking-actions">
                            <button type="submit" class="time-tracking-save">Save</button>
                            <button type="button" class="time-tracking-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="due-date details-item">
            <div class="title">due date</div>
            <?php $dateString = $fields['duedate'] ?? '';
            $currentDate = date('Y-m-d'); ?>
            <div
                class="dueDate dueDate-item item-info <?= ($dateString && $currentDate >= $dateString) ? 'deadline' : '' ?>"
                data-issue-key="<?= htmlspecialchars($issueKey) ?>"
                data-date="<?= htmlspecialchars($dateString) ?>">
                <p>
                    <svg width="16" height="16" viewBox="0 0 24 24" role="presentation">
                        <path
                            d="M4.995 5h14.01C20.107 5 21 5.895 21 6.994v12.012A1.994 1.994 0 0119.005 21H4.995A1.995 1.995 0 013 19.006V6.994C3 5.893 3.892 5 4.995 5zM5 9v9a1 1 0 001 1h12a1 1 0 001-1V9H5zm1-5a1 1 0 012 0v1H6V4zm10 0a1 1 0 012 0v1h-2V4zm-9 9v-2.001h2V13H7zm8 0v-2.001h2V13h-2zm-4 0v-2.001h2.001V13H11zm-4 4v-2h2v2H7zm4 0v-2h2.001v2H11zm4 0v-2h2v2h-2z"
                            fill="currentColor" fill-rule="evenodd"></path>
                    </svg>
                    <span><?= htmlspecialchars($dateString ? popupFormatDate($dateString) : '') ?></span>
                </p>
                <input type="date" class="dueDate-input" value="<?= htmlspecialchars($dateString) ?>">
            </div>
        </div>
    </div>
</div>

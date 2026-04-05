<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="app dashboard-app-shell attachments-page-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main attachments-main">
        <section class="dashboard-topbar attachments-topbar">
            <div class="dashboard-heading-block">
                <h2>Attachments</h2>
            </div>
            <div class="dashboard-topbar-actions">
                <button type="button" class="dashboard-button" id="attachmentsRefresh">Refresh</button>
                <a href="/board" class="dashboard-button dashboard-button-dark">Open Tasks</a>
                <a href="/create-task" class="dashboard-button dashboard-button-primary"><i class="fa-solid fa-plus"></i>Create Task</a>
            </div>
        </section>

        <section class="panel attachments-toolbar-panel">
            <div class="attachments-toolbar-grid">
                <div class="attachments-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="attachmentsSearch" placeholder="Search file name, issue key, or issue summary...">
                </div>
                <select id="attachmentsTypeFilter">
                    <option value="all">All file types</option>
                    <option value="image">Images</option>
                    <option value="document">Documents</option>
                    <option value="archive">Archives</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </section>

        <section class="attachments-summary-grid" id="attachmentsSummary"></section>

        <section class="panel attachments-library-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Attachment Library</h3>
                </div>
                <span class="dashboard-chip" id="attachmentsCountChip">0 files</span>
            </div>
            <div class="panel-body">
                <div class="attachments-grid" id="attachmentsGrid"></div>
                <div class="attachments-empty-state" id="attachmentsEmptyState" style="display:none;">
                    <div class="attachments-empty-icon"><i class="fa-regular fa-folder-open"></i></div>
                    <h4>No attachments found</h4>
                    <p>Try a different search term or file type filter.</p>
                </div>
            </div>
        </section>

        <div class="loading-chart dashboard-loading"><img src="../../assets/images/loader-1.gif" alt="Loading"></div>
    </main>
</div>

<script src="/assets/js/attachments.js?ver=<?php random(); ?>"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

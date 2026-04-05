<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="app dashboard-app-shell archived-items-page-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main archived-items-main">
        <section class="dashboard-topbar archived-items-topbar">
            <div class="dashboard-heading-block">
                <h2>Archived Work Items</h2>
                <p>Browse work items archived from this app, search them quickly, and review who archived them.</p>
            </div>
            <div class="dashboard-topbar-actions">
                <a href="/board" class="dashboard-button">Open Tasks</a>
                <a href="/create-task" class="dashboard-button dashboard-button-primary"><i class="fa-solid fa-plus"></i>Create Task</a>
            </div>
        </section>

        <section class="panel archived-toolbar-panel">
            <div class="archived-toolbar-grid">
                <div class="archived-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="archivedSearch" placeholder="Search work items, keys, summary, reporter...">
                </div>

                <div class="archived-toolbar-right">
                    <label class="archived-limit-wrap">
                        <span>Show</span>
                        <select id="archivedLimit">
                            <option value="20">20</option>
                            <option value="40">40</option>
                            <option value="60">60</option>
                            <option value="100">100</option>
                        </select>
                    </label>
                    <button type="button" class="dashboard-button" id="archivedRefresh">Refresh</button>
                </div>
            </div>
        </section>

        <section class="archived-summary-grid" id="archivedSummary"></section>

        <section class="panel archived-list-panel">
            <div class="panel-header archived-panel-header">
                <div>
                    <h3 class="panel-title">Archive history</h3>
                    <p class="panel-subtitle">Items are listed from newest to oldest based on their archived date.</p>
                </div>
                <span class="dashboard-chip" id="archivedCountChip">0 items</span>
            </div>
            <div class="panel-body archived-table-wrap">
                <div class="archived-table-scroller">
                    <table class="archived-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Key</th>
                                <th>Summary</th>
                                <th>Reporter</th>
                                <th>Date archived</th>
                                <th>Archived by</th>
                            </tr>
                        </thead>
                        <tbody id="archivedTableBody"></tbody>
                    </table>
                </div>

                <div class="archived-mobile-list" id="archivedMobileList"></div>

                <div class="archived-empty-state" id="archivedEmptyState" style="display:none;">
                    <div class="archived-empty-icon"><i class="fa-regular fa-box-archive"></i></div>
                    <h4>No archived work items found</h4>
                    <p>Archive a work item from the board to see it listed here.</p>
                </div>
            </div>
            <div class="archived-pagination" id="archivedPagination"></div>
        </section>

        <div class="loading-chart dashboard-loading"><img src="../../assets/images/loader-1.gif" alt="Loading"></div>
    </main>
</div>

<script>
    window.archivedWorkItemsConfig = {
        listUrl: '/api/archived-work-items'
    };
</script>
<script src="/assets/js/archived-work-items.js?ver=<?php random(); ?>"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

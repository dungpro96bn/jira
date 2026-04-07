<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="app dashboard-app-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main">
        <section class="dashboard-topbar">
            <div class="dashboard-heading-block">
                <h2>Dashboard</h2>
                <p>Track progress, workload, overdue issues, and team activity.</p>
            </div>
            <div class="dashboard-topbar-actions">
                <button type="button" class="dashboard-button" id="dashboardClearCache">Clear Cache</button>
                <a href="/board" class="dashboard-button dashboard-button-dark">Open Tasks</a>
                <a href="/create-task" class="dashboard-button dashboard-button-primary"><i class="fa-solid fa-plus"></i>Create Task</a>
            </div>
        </section>

        <section class="dashboard-toolbar panel">
            <div class="dashboard-toolbar-grid">
                <input id="dashboardSearch" type="text" placeholder="Search issue key or summary...">
                <select id="dashboardAssignee">
                    <option value="all">All assignees</option>
                </select>
                <select id="dashboardStatus">
                    <option value="all">All statuses</option>
                </select>
                <select id="dashboardRange">
                    <option value="7">Last 7 days</option>
                    <option value="14">Last 14 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="180">Last 6 months</option>
                    <option value="365">Last 1 year</option>
                    <option value="730">Last 2 years</option>
                    <option value="3650">All time</option>
                </select>
                <button type="button" class="dashboard-button" id="dashboardRefresh">Refresh</button>
            </div>
        </section>

        <section class="dashboard-stats-grid" id="dashboardStats"></section>

        <section class="dashboard-content-grid dashboard-content-grid-2">
            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Task Status Overview</h3>
                        <p class="panel-subtitle">Distribution of issues across workflow statuses</p>
                    </div>
                    <span class="dashboard-chip" id="dashboardGeneratedAt">Live data</span>
                </div>
                <div class="summary-box panel-body">
                    <div class="chart-wrap">
                        <canvas id="dashboardStatusChart" width="300" height="300"></canvas>
                        <div class="total">
                            <span id="totalCount">0</span>
                            <p>Total work items</p>
                        </div>
                    </div>
                    <ul class="legend" id="statusLegend"></ul>
                </div>
            </article>

            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Workload by Assignee</h3>
                        <p class="panel-subtitle">Number of visible tasks assigned to each teammate</p>
                    </div>
                    <span class="dashboard-chip">Filtered view</span>
                </div>
                <div class="panel-body">
                    <div class="chart-wrap">
                        <canvas id="dashboardWorkloadChart"></canvas>
                    </div>
                    <ul class="legend" id="statusLegend"></ul>
                </div>
            </article>
        </section>

        <section class="dashboard-content-grid dashboard-content-grid-2">
            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Priority Breakdown</h3>
                        <p class="panel-subtitle">How work is distributed by priority level</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="chart-wrap chart-wrap-bar">
                        <canvas id="dashboardPriorityChart"></canvas>
                    </div>
                </div>
            </article>

            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Types of Work</h3>
                        <p class="panel-subtitle">High-level breakdown by issue type</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-list" id="dashboardTypesList"></div>
                </div>
            </article>
        </section>

        <section class="dashboard-content-grid dashboard-content-grid-2">
            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Recent Tasks</h3>
                        <p class="panel-subtitle">Filtered by the controls above</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-table-wrap">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Issue</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due</th>
                                </tr>
                            </thead>
                            <tbody id="dashboardRecentTasks"></tbody>
                        </table>
                    </div>
                </div>
            </article>

            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Needs Attention</h3>
                        <p class="panel-subtitle">Quick focus list for admin and editor</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-list" id="dashboardAttentionList"></div>
                </div>
            </article>
        </section>

        <section class="dashboard-content-grid dashboard-content-grid-2">
            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Team Snapshot</h3>
                        <p class="panel-subtitle">Simple overview of active distribution</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-list" id="dashboardTeamSnapshot"></div>
                </div>
            </article>

            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Quick Actions</h3>
                        <p class="panel-subtitle">Shortcuts you can wire into your current routes</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="dashboard-actions-grid">
                        <a class="quick-action-card" href="/create-task">
                            <span class="quick-action-card__icon"><i class="fa-solid fa-plus"></i></span>
                            <div>
                                <strong>Create Task</strong>
                                <p>Open your create task popup or page.</p>
                            </div>
                        </a>
                        <a class="quick-action-card" href="/board">
                            <span class="quick-action-card__icon">☰</span>
                            <div>
                                <strong>Open Tasks</strong>
                                <p>Jump to kanban board for drag-and-drop actions.</p>
                            </div>
                        </a>
                        <a class="quick-action-card" href="/users">
                            <span class="quick-action-card__icon"><i class="fa-solid fa-user"></i></span>
                            <div>
                                <strong>Manage Users</strong>
                                <p>Review team roles and approvals.</p>
                            </div>
                        </a>
                        <button type="button" class="quick-action-card quick-action-card--button" id="dashboardRefreshSecondary">
                            <span class="quick-action-card__icon"><i class="fa-solid fa-arrows-rotate"></i></span>
                            <div>
                                <strong>Refresh Dashboard</strong>
                                <p>Reload the latest Jira statistics.</p>
                            </div>
                        </button>
                    </div>
                </div>
            </article>
        </section>

        <div class="loading-chart dashboard-loading"><img src="../../assets/images/loader-1.gif" alt="Loading"></div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/dashboard.js?ver=<?php random(); ?>"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

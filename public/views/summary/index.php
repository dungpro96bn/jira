<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="app dashboard-app-shell">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="dashboard-main dashboard-summary-page">
        <section class="dashboard-topbar">
            <div class="dashboard-heading-block">
                <h2>Summary</h2>
                <p>See the original summary widgets inside the same dashboard layout and sidebar structure.</p>
            </div>
            <div class="dashboard-topbar-actions">
                <button id="refreshSummary" class="dashboard-button">Refresh</button>
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/dashboard" class="dashboard-button dashboard-button-primary">Dashboard</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-content-grid dashboard-content-grid-2">
            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Status overview</h3>
                        <p class="panel-subtitle">Get a snapshot of the status of your work items.</p>
                    </div>
                </div>
                <div class="panel-body summary-chart-panel">
                    <div class="chart-wrap summary-donut-wrap">
                        <canvas id="statusChart" width="320" height="320"></canvas>
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
                        <h3 class="panel-title">Priority breakdown</h3>
                        <p class="panel-subtitle">Get a holistic view of how work is being prioritized.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="priority-chart dashboard-summary-chart">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>
            </article>
        </section>

        <section class="dashboard-content-grid dashboard-content-grid-2">
            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Team workload</h3>
                        <p class="panel-subtitle">Monitor the capacity of your team.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="workloadList"></div>
                </div>
            </article>

            <article class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Types of work</h3>
                        <p class="panel-subtitle">Get a breakdown of work items by their types.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="typesList"></div>
                </div>
            </article>
        </section>

        <div class="loading-chart"><img src="../../assets/images/loader-1.gif" alt=""></div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/summary.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

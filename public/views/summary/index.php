<?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="main">
        <div class="inner">
            <div class="container-panel">

                <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

                <div class="column-main">
                    <button id="refreshSummary">Refresh</button>

                    <div class="summary-block">
                        <div class="summary-title">
                            <h2>Status overview</h2>
                            <p>Get a snapshot of the status of your work items.</p>
                        </div>
                        <div class="summary-box">
                            <div class="chart-wrap">
                                <canvas id="statusChart" width="300" height="300"></canvas>

                                <div class="total">
                                    <span id="totalCount">0</span>
                                    <p>Total work items</p>
                                </div>
                            </div>
                            <ul class="legend" id="statusLegend"></ul>
                        </div>
                    </div>

                    <div class="summary-block">
                        <div class="summary-title">
                            <h2>Priority breakdown</h2>
                            <p>Get a holistic view of how work is being prioritized.</p>
                        </div>
                        <div class="priority-chart">
                            <canvas id="priorityChart"></canvas>
                        </div>
                    </div>

                    <div class="summary-block">
                        <div class="summary-title">
                            <h2>Team workload</h2>
                            <p>Monitor the capacity of your team.</p>
                        </div>
                        <div id="workloadList"></div>
                    </div>

                    <div class="summary-block">
                        <div class="summary-title">
                            <h2>Types of work</h2>
                            <p>Get a breakdown of work items by their types.</p>
                        </div>
                        <div id="typesList"></div>
                    </div>

                    <div class="loading-chart"><img src="../../assets/images/loader-1.gif" alt=""></div>

                </div>

            </div>
        </div>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/assets/js/summary.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
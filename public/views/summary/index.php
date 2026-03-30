<?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="main">
        <div class="inner">
            <div class="container-panel">

                <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

                <div class="column-main">
                    <div class="summary-page">

                        <div class="top-title">
                            <h2>Status overview</h2>
                        </div>

                        <div class="summary-box">
                            <div class="chart-wrap">
                                <canvas id="statusChart"></canvas>

                                <div class="total">
                                    <span id="totalCount">0</span>
                                    <p>Total work items</p>
                                </div>
                            </div>
                            <ul class="legend" id="statusLegend"></ul>
                        </div>

                        <button id="refreshSummary">Refresh</button>

                    </div>

                    <div class="loading-chart"><img src="../../assets/images/loader-1.gif" alt=""></div>

                </div>

            </div>
        </div>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/assets/js/summary.js"></script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
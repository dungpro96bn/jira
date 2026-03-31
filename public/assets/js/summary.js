$(function () {

    /*
    |--------------------------------------------------------------------------
    | CONFIG CACHE
    |--------------------------------------------------------------------------
    */
    const EXPIRE = 5 * 60 * 1000; // 5 phút

    const CACHE = {
        SUMMARY: 'summary_cache',
        SUMMARY_TIME: 'summary_cache_time',

        PRIORITY: 'priority_cache',
        PRIORITY_TIME: 'priority_cache_time',

        WORKLOAD_CACHE: 'workload_cache',
        WORKLOAD_CACHE_TIME: 'workload_cache_time',

        TYPES_CACHE: 'types_cache',
        TYPES_CACHE_TIME: 'types_cache_time'
    };


    /*
    |--------------------------------------------------------------------------
    | LOAD STATUS (SUMMARY)
    |--------------------------------------------------------------------------
    */
    function loadSummary() {

        const now = Date.now();

        const cacheData = localStorage.getItem(CACHE.SUMMARY);
        const cacheTime = localStorage.getItem(CACHE.SUMMARY_TIME);

        $('.loading-chart').addClass('active');

        // dùng cache
        if (cacheData && cacheTime && (now - cacheTime < EXPIRE)) {
            console.log('Load summary from cache');
            renderSummary(JSON.parse(cacheData));
            $('.loading-chart').removeClass('active');
            return;
        }

        // gọi API
        $.ajax({
            url: '/api/summary',
            method: 'GET',
            dataType: 'json',

            success: function (res) {

                localStorage.setItem(CACHE.SUMMARY, JSON.stringify(res));
                localStorage.setItem(CACHE.SUMMARY_TIME, now);

                renderSummary(res);
            },

            error: function (xhr) {
                console.error('Summary API error:', xhr.responseText);
            },

            complete: function () {
                $('.loading-chart').removeClass('active');
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD PRIORITY
    |--------------------------------------------------------------------------
    */
    function loadPriority() {

        const now = Date.now();

        const cacheData = localStorage.getItem(CACHE.PRIORITY);
        const cacheTime = localStorage.getItem(CACHE.PRIORITY_TIME);

        // dùng cache
        if (cacheData && cacheTime && (now - cacheTime < EXPIRE)) {
            console.log('Load priority from cache');
            renderPriority(JSON.parse(cacheData));
            return;
        }

        // gọi API
        $.ajax({
            url: '/api/summary/priority',
            method: 'GET',
            dataType: 'json',

            success: function (res) {

                if (!res.success) {
                    console.error(res.error);
                    return;
                }

                localStorage.setItem(CACHE.PRIORITY, JSON.stringify(res));
                localStorage.setItem(CACHE.PRIORITY_TIME, now);

                renderPriority(res);
            },

            error: function (xhr) {
                console.error('Priority API error:', xhr.responseText);
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER STATUS
    |--------------------------------------------------------------------------
    */
    function renderSummary(res) {

        if (!res || !res.status) {
            console.error('Invalid summary data', res);
            return;
        }

        const labels = Object.keys(res.status);
        const values = Object.values(res.status);

        $('#totalCount').text(res.total);

        const colors = [
            '#964AC0',
            '#1558BC',
            '#BF63F3',
            '#357DE8',
            '#82B536',
            '#F68909',
            '#42B2D7',
            '#989898'
        ];

        // destroy chart cũ
        if (window.statusChartInstance) {
            window.statusChartInstance.destroy();
        }

        window.statusChartInstance = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors
                }]
            },
            options: {
                cutout: '76%',
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // legend
        let html = '';
        labels.forEach((label, i) => {
            html += `
                <li>
                    <span style="background:${colors[i]}"></span>
                    ${label}: ${values[i]}
                </li>
            `;
        });

        $('#statusLegend').html(html);
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER PRIORITY
    |--------------------------------------------------------------------------
    */
    function renderPriority(res) {

        const ctx = document.getElementById('priorityChart');

        if (window.priorityChartInstance) {
            window.priorityChartInstance.destroy();
        }

        window.priorityChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: res.labels,
                datasets: [{
                    label: 'Tasks by Priority',
                    data: res.data,
                    borderWidth: 1,
                    backgroundColor: [
                        '#d32f2f',
                        '#f44336',
                        '#fbc02d',
                        '#4caf50',
                        '#90a4ae'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }


    function loadWorkload() {

        const now = Date.now();
        const EXPIRE = 5 * 60 * 1000;

        const cacheData = localStorage.getItem(CACHE.WORKLOAD_CACHE);
        const cacheTime = localStorage.getItem(CACHE.WORKLOAD_CACHE_TIME);

        if (cacheData && cacheTime && (now - cacheTime < EXPIRE)) {
            renderWorkload(JSON.parse(cacheData));
            return;
        }

        $.ajax({
            url: '/api/summary/workload',
            method: 'GET',
            dataType: 'json',

            success: function (res) {

                if (!res.success) return;

                localStorage.setItem(CACHE.WORKLOAD_CACHE, JSON.stringify(res));
                localStorage.setItem(CACHE.WORKLOAD_CACHE_TIME, now);

                renderWorkload(res);
            },

            error: function (xhr) {
                console.error('Priority API error:', xhr.responseText);
            }

        });
    }

    function renderWorkload(res) {

        let html = '';

        res.data.forEach(user => {

            html += `
            <div class="workload-item">

                <div class="workload-user">
                    <img src="${user.avatar}" />
                    <span>${user.name}</span>
                </div>

                <div class="workload-bar" title="${user.percent}% (${user.count} / ${user.countTotal} work items)">
                    <div class="workload-bar-inner" style="width:${user.percent}%"></div>
                </div>

                <div class="workload-percent">
                    ${user.percent}%
                </div>

            </div>
        `;
        });

        $('#workloadList').html(html);
    }


    function loadTypes() {

        const now = Date.now();
        const EXPIRE = 5 * 60 * 1000;

        const cacheData = localStorage.getItem(CACHE.TYPES_CACHE);
        const cacheTime = localStorage.getItem(CACHE.TYPES_CACHE_TIME);

        if (cacheData && cacheTime && (now - cacheTime < EXPIRE)) {
            renderTypes(JSON.parse(cacheData));
            return;
        }

        $.ajax({
            url: '/api/summary/types',
            method: 'GET',
            dataType: 'json',

            success: function (res) {

                if (!res.success) return;

                localStorage.setItem(CACHE.TYPES_CACHE, JSON.stringify(res));
                localStorage.setItem(CACHE.TYPES_CACHE_TIME, now);

                renderTypes(res);
            }
        });
    }

    function renderTypes(res) {

        let html = '';

        res.data.forEach(item => {

            html += `
            <div class="types-item">

                <div class="types-name">
                    ${item.name}
                </div>

                <div class="types-bar" title="${item.percent}% (${item.count} / ${item.countTotal} work items)">
                    <div class="types-bar-inner" style="width:${item.percent}%"></div>
                </div>

                <div class="types-percent">
                    ${item.percent}%
                </div>

            </div>
        `;
        });

        $('#typesList').html(html);
    }


    /*
    |--------------------------------------------------------------------------
    | REFRESH BUTTON
    |--------------------------------------------------------------------------
    */
    $('#refreshSummary').on('click', function () {

        $('.loading-chart').addClass('active');

        // clear cache FE
        Object.values(CACHE).forEach(key => localStorage.removeItem(key));

        // clear cache BE (nếu có)
        $.ajax({
            url: '/api/summary/clear',
            method: 'GET'
        })
            .then(() => {

                return $.ajax({
                    url: '/api/summary',
                    method: 'GET',
                    dataType: 'json'
                });

            })
            .then(res => {

                renderSummary(res);
                loadPriority(); // reload priority

            })
            .always(() => {
                $('.loading-chart').removeClass('active');
            });

    });


    /*
    |--------------------------------------------------------------------------
    | INIT LOAD
    |--------------------------------------------------------------------------
    */
    loadSummary();
    loadPriority();
    loadWorkload();
    loadTypes();

});
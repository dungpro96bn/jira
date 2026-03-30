$(function () {

    $('#refreshSummary').on('click', function () {

        $('.loading-chart').addClass('active');

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
                render(res);
            })
            .fail(err => {
                console.error('Error:', err);
            })
            .always(() => {
                $('.loading-chart').removeClass('active');
            });

    });


    const CACHE_KEY = 'summary_cache';
    const CACHE_TIME_KEY = 'summary_cache_time';
    const EXPIRE = 5 * 60 * 1000; // 5 phút

    const now = Date.now();
    const cacheData = localStorage.getItem(CACHE_KEY);
    const cacheTime = localStorage.getItem(CACHE_TIME_KEY);

    $('.loading-chart').addClass('active');

    // dùng cache nếu còn hạn
    if (cacheData && cacheTime && (now - cacheTime < EXPIRE)) {
        console.log('Load summary from cache');
        render(JSON.parse(cacheData));
        $('.loading-chart').removeClass('active');
        return;
    }

    // gọi API nếu không có cache
    $.ajax({
        url: '/api/summary',
        method: 'GET',
        dataType: 'json',
        success: function (res) {

            // lưu cache
            localStorage.setItem(CACHE_KEY, JSON.stringify(res));
            localStorage.setItem(CACHE_TIME_KEY, now);

            render(res);
            $('.loading-chart').removeClass('active');
        },
        error: function (xhr, status, error) {
            console.error('API error:', error);
            $('.loading-chart').removeClass('active');
        }
    });

    function render(res) {

        if (!res || !res.status) {
            console.error('Invalid data', res);
            return;
        }

        const data = res.status;

        const labels = Object.keys(data);
        const values = Object.values(data);

        if (!labels.length) {
            console.warn('No data');
            return;
        }

        $('#totalCount').text(res.total);

        const colors = [
            '#4ba3c3',
            '#8e44ad',
            '#f39c12',
            '#27ae60',
            '#2980b9',
            '#e67e22',
            '#2ecc71',
            '#989898'
        ];

        // ❗ tránh tạo nhiều chart bị chồng
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
                cutout: '70%',
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




});
$(function () {
    var CACHE_KEY = 'jira_dashboard_cache_v1';
    var CACHE_TIME_KEY = 'jira_dashboard_cache_time_v1';
    var CACHE_EXPIRE = 5 * 60 * 1000;
    var dashboardData = null;
    var statusChartInstance = null;
    var workloadChartInstance = null;
    var priorityChartInstance = null;

    function escapeHtml(text) {
        return String(text === null || typeof text === 'undefined' ? '' : text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getTodayDate() {
        var now = new Date();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        return now.getFullYear() + '-' + month + '-' + day;
    }

    function formatDate(dateString) {
        if (!dateString) {
            return 'No due date';
        }

        var date = new Date(dateString + 'T00:00:00');
        if (isNaN(date.getTime())) {
            return dateString;
        }

        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function daysBetween(dateString) {
        if (!dateString) {
            return null;
        }

        var target = new Date(dateString + 'T00:00:00');
        var today = new Date(getTodayDate() + 'T00:00:00');

        if (isNaN(target.getTime())) {
            return null;
        }

        return Math.floor((target.getTime() - today.getTime()) / 86400000);
    }

    function createdWithinRange(created, range) {
        if (!created || range === 3650) {
            return true;
        }

        var createdDate = new Date(created + 'T00:00:00');
        var today = new Date(getTodayDate() + 'T00:00:00');

        if (isNaN(createdDate.getTime())) {
            return false;
        }

        var diff = Math.floor((today.getTime() - createdDate.getTime()) / 86400000);
        return diff <= range;
    }

    function statusClass(status) {
        var value = (status || '').toLowerCase();

        if (value.indexOf('done') !== -1 || value.indexOf('delivered') !== -1) {
            return 'is-done';
        }
        if (value.indexOf('progress') !== -1 || value.indexOf('review') !== -1 || value.indexOf('check') !== -1) {
            return 'is-progress';
        }
        if (value.indexOf('pending') !== -1 || value.indexOf('wait') !== -1) {
            return 'is-pending';
        }
        if (value.indexOf('todo') !== -1 || value.indexOf('to do') !== -1 || value.indexOf('open') !== -1) {
            return 'is-todo';
        }
        return 'is-neutral';
    }

    function priorityClass(priority) {
        var value = (priority || '').toLowerCase();
        if (value.indexOf('highest') !== -1 || value.indexOf('high') !== -1) {
            return 'is-high';
        }
        if (value.indexOf('medium') !== -1) {
            return 'is-medium';
        }
        if (value.indexOf('low') !== -1 || value.indexOf('lowest') !== -1) {
            return 'is-low';
        }
        return 'is-neutral';
    }

    function getFilters() {
        return {
            search: $('#dashboardSearch').val().trim().toLowerCase(),
            assignee: $('#dashboardAssignee').val(),
            status: $('#dashboardStatus').val(),
            range: parseInt($('#dashboardRange').val(), 10)
        };
    }

    function getFilteredIssues() {
        if (!dashboardData || !dashboardData.issues) {
            return [];
        }

        var filters = getFilters();

        return dashboardData.issues.filter(function (issue) {
            var matchesSearch = !filters.search
                || issue.key.toLowerCase().indexOf(filters.search) !== -1
                || issue.summary.toLowerCase().indexOf(filters.search) !== -1;
            var matchesAssignee = filters.assignee === 'all' || issue.assignee === filters.assignee;
            var matchesStatus = filters.status === 'all' || issue.status === filters.status;
            var matchesRange = createdWithinRange(issue.created, filters.range);

            return matchesSearch && matchesAssignee && matchesStatus && matchesRange;
        });
    }

    function groupedCounts(items, key) {
        var result = {};
        items.forEach(function (item) {
            var name = item[key] || 'Unknown';
            if (!result[name]) {
                result[name] = 0;
            }
            result[name] += 1;
        });
        return result;
    }

    function renderStats(issues) {
        var statusCounts = groupedCounts(issues, 'status');
        var total = issues.length;
        var overdue = issues.filter(function (issue) {
            var diff = daysBetween(issue.duedate);
            return diff !== null && diff < 0 && (issue.status || '').toLowerCase().indexOf('done') === -1;
        }).length;
        var done = issues.filter(function (issue) {
            return (issue.status || '').toLowerCase().indexOf('done') !== -1;
        }).length;
        var progress = issues.filter(function (issue) {
            return (issue.status || '').toLowerCase().indexOf('progress') !== -1;
        }).length;
        var completionRate = total > 0 ? Math.round((done / total) * 100) : 0;

        var cards = [
            { title: 'Total Tasks', value: total, meta: 'Visible after filters', tone: 'tone-blue' },
            { title: 'In Progress', value: progress, meta: 'Currently active work', tone: 'tone-amber' },
            { title: 'Done', value: done, meta: 'Completed issues', tone: 'tone-green' },
            { title: 'Overdue', value: overdue, meta: 'Past due and unfinished', tone: 'tone-red' },
            { title: 'Completion Rate', value: completionRate + '%', meta: 'Done vs visible total', tone: 'tone-purple' },
            { title: 'Statuses', value: Object.keys(statusCounts).length, meta: 'Different workflow stages', tone: 'tone-cyan' }
        ];

        var html = '';
        cards.forEach(function (card) {
            html += '<article class="dashboard-stat-card ' + card.tone + '">';
            html += '<div class="dashboard-stat-card__label">' + escapeHtml(card.title) + '</div>';
            html += '<div class="dashboard-stat-card__value">' + escapeHtml(card.value) + '</div>';
            html += '<div class="dashboard-stat-card__meta">' + escapeHtml(card.meta) + '</div>';
            html += '</article>';
        });

        $('#dashboardStats').html(html);
    }

    function buildStatusChart(issues) {

        if (!issues || !issues.length) {
            console.error('Invalid issues data', issues);
            return;
        }

        var counts = groupedCounts(issues, 'status');
        var labels = Object.keys(counts);
        var values = Object.values(counts);
        var colors = [
            '#964AC0',
            '#1558BC',
            '#BF63F3',
            '#357DE8',
            '#82B536',
            '#F68909',
            '#42B2D7',
            '#989898'
        ];

        if (statusChartInstance) {
            statusChartInstance.destroy();
        }

        $('#totalCount').text(issues.length);

        statusChartInstance = new Chart(document.getElementById('dashboardStatusChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        var html = '';
        labels.forEach(function (label, i) {
            html += `
            <li>
                <span style="background:${colors[i]}"></span>
                ${label}: ${values[i]}
            </li>
        `;
        });

        $('#statusLegend').html(html);
    }

    function buildWorkloadChart(issues) {
        var grouped = {};
        issues.forEach(function (issue) {
            if (!grouped[issue.assignee]) {
                grouped[issue.assignee] = 0;
            }
            grouped[issue.assignee] += 1;
        });

        var entries = Object.keys(grouped).map(function (name) {
            return { name: name, count: grouped[name] };
        }).sort(function (a, b) {
            return b.count - a.count;
        }).slice(0, 8);

        if (workloadChartInstance) {
            workloadChartInstance.destroy();
        }

        workloadChartInstance = new Chart(document.getElementById('dashboardWorkloadChart'), {
            type: 'bar',
            data: {
                labels: entries.map(function (item) { return item.name; }),
                datasets: [{
                    data: entries.map(function (item) { return item.count; }),
                    backgroundColor: ['#2563eb', '#7c3aed', '#0891b2', '#16a34a', '#d97706', '#ef4444', '#0f766e', '#475569'],
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function buildPriorityChart(issues) {
        var counts = groupedCounts(issues, 'priority');
        var order = ['Highest', 'High', 'Medium', 'Low', 'Lowest', 'No Priority'];
        var labels = Object.keys(counts).sort(function (a, b) {
            var aIndex = order.indexOf(a);
            var bIndex = order.indexOf(b);
            aIndex = aIndex === -1 ? 999 : aIndex;
            bIndex = bIndex === -1 ? 999 : bIndex;
            return aIndex - bIndex;
        });

        if (priorityChartInstance) {
            priorityChartInstance.destroy();
        }

        priorityChartInstance = new Chart(document.getElementById('dashboardPriorityChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: labels.map(function (label) { return counts[label]; }),
                    backgroundColor: ['#b91c1c', '#ef4444', '#f59e0b', '#22c55e', '#94a3b8', '#cbd5e1'],
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function renderTypes(issues) {
        var counts = groupedCounts(issues, 'issueType');
        var total = issues.length;
        var entries = Object.keys(counts).map(function (name) {
            return {
                name: name,
                count: counts[name],
                percent: total > 0 ? Math.round((counts[name] / total) * 100) : 0
            };
        }).sort(function (a, b) {
            return b.count - a.count;
        });

        var html = '';
        entries.forEach(function (entry) {
            html += '<div class="dashboard-list-item">';
            html += '  <div class="dashboard-list-item__main">';
            html += '      <strong>' + escapeHtml(entry.name) + '</strong>';
            html += '      <span>' + escapeHtml(entry.count + ' tasks') + '</span>';
            html += '  </div>';
            html += '  <div class="dashboard-progress">';
            html += '      <div class="dashboard-progress__bar"><span style="width:' + entry.percent + '%"></span></div>';
            html += '      <em>' + entry.percent + '%</em>';
            html += '  </div>';
            html += '</div>';
        });

        $('#dashboardTypesList').html(html || '<div class="dashboard-empty">No type data available.</div>');
    }

    function renderRecentTasks(issues) {
        var rows = issues.slice().sort(function (a, b) {
            var aDate = a.created ? new Date(a.created).getTime() : 0;
            var bDate = b.created ? new Date(b.created).getTime() : 0;
            return bDate - aDate;
        }).slice(0, 8);

        var html = '';
        rows.forEach(function (issue) {
            html += '<tr>';
            html += '  <td>';
            html += '      <a href="https://dev-scvweb.atlassian.net/browse/' + encodeURIComponent(issue.key) + '" target="_blank" class="dashboard-issue-key">' + escapeHtml(issue.key) + '</a>';
            html += '      <div class="dashboard-issue-summary">' + escapeHtml(issue.summary) + '</div>';
            html += '  </td>';
            html += '  <td>';
            html += '      <div class="dashboard-assignee">';
            html += '          <img src="' + escapeHtml(issue.assigneeAvatar || '/assets/images/default-avatar.jpg') + '" alt="">';
            html += '          <span>' + escapeHtml(issue.assignee) + '</span>';
            html += '      </div>';
            html += '  </td>';
            html += '  <td><span class="dashboard-badge ' + statusClass(issue.status) + '">' + escapeHtml(issue.status) + '</span></td>';
            html += '  <td><span class="dashboard-priority ' + priorityClass(issue.priority) + '">' + escapeHtml(issue.priority) + '</span></td>';
            html += '  <td>' + escapeHtml(formatDate(issue.duedate)) + '</td>';
            html += '</tr>';
        });

        $('#dashboardRecentTasks').html(html || '<tr><td colspan="5" class="dashboard-empty">No tasks match the current filters.</td></tr>');
    }

    function renderAttention(issues) {
        var items = issues.filter(function (issue) {
            var diff = daysBetween(issue.duedate);
            return diff !== null && (diff <= 2) && (issue.status || '').toLowerCase().indexOf('done') === -1;
        }).map(function (issue) {
            var diff = daysBetween(issue.duedate);
            var label = 'Due soon';
            var cls = 'is-todo';
            if (diff < 0) {
                label = Math.abs(diff) + ' day' + (Math.abs(diff) > 1 ? 's' : '') + ' overdue';
                cls = 'is-overdue';
            } else if (diff === 0) {
                label = 'Due today';
                cls = 'is-progress';
            } else {
                label = 'Due in ' + diff + ' day' + (diff > 1 ? 's' : '');
            }
            return {
                issue: issue,
                label: label,
                cls: cls,
                diff: diff
            };
        }).sort(function (a, b) {
            return a.diff - b.diff;
        }).slice(0, 8);

        var html = '';
        items.forEach(function (item) {
            html += '<div class="dashboard-list-item dashboard-list-item--compact">';
            html += '  <div class="dashboard-list-item__main">';
            html += '      <strong>' + escapeHtml(item.issue.key + ' · ' + item.issue.summary) + '</strong>';
            html += '      <span>' + escapeHtml(item.issue.assignee + ' · ' + formatDate(item.issue.duedate)) + '</span>';
            html += '  </div>';
            html += '  <span class="dashboard-badge ' + item.cls + '">' + escapeHtml(item.label) + '</span>';
            html += '</div>';
        });

        $('#dashboardAttentionList').html(html || '<div class="dashboard-empty">No urgent items for the current filters.</div>');
    }

    function renderTeamSnapshot(issues) {
        var grouped = {};
        issues.forEach(function (issue) {
            if (!grouped[issue.assignee]) {
                grouped[issue.assignee] = {
                    name: issue.assignee,
                    avatar: issue.assigneeAvatar || '/assets/images/default-avatar.jpg',
                    count: 0
                };
            }
            grouped[issue.assignee].count += 1;
        });

        var entries = Object.keys(grouped).map(function (name) {
            return grouped[name];
        }).sort(function (a, b) {
            return b.count - a.count;
        });

        var html = '';
        entries.forEach(function (entry) {
            html += '<div class="dashboard-list-item dashboard-list-item--member">';
            html += '  <div class="dashboard-assignee">';
            html += '      <img src="' + escapeHtml(entry.avatar) + '" alt="">';
            html += '      <div><strong>' + escapeHtml(entry.name) + '</strong><span>Visible workload</span></div>';
            html += '  </div>';
            html += '  <span class="dashboard-count-pill">' + escapeHtml(entry.count + ' tasks') + '</span>';
            html += '</div>';
        });

        $('#dashboardTeamSnapshot').html(html || '<div class="dashboard-empty">No team snapshot data available.</div>');
    }

    function renderGeneratedAt() {
        if (!dashboardData || !dashboardData.generated_at) {
            $('#dashboardGeneratedAt').text('Live data');
            return;
        }

        var date = new Date(dashboardData.generated_at);
        $('#dashboardGeneratedAt').text('Updated ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
    }

    function renderFilters() {
        if (!dashboardData || !dashboardData.issues) {
            return;
        }

        var issues = dashboardData.issues;
        var assignees = [];
        var statuses = [];

        issues.forEach(function (issue) {
            if (assignees.indexOf(issue.assignee) === -1) {
                assignees.push(issue.assignee);
            }
            if (statuses.indexOf(issue.status) === -1) {
                statuses.push(issue.status);
            }
        });

        assignees.sort();
        statuses.sort();

        var assigneeValue = $('#dashboardAssignee').val() || 'all';
        var statusValue = $('#dashboardStatus').val() || 'all';

        $('#dashboardAssignee').html('<option value="all">All assignees</option>' + assignees.map(function (name) {
            return '<option value="' + escapeHtml(name) + '">' + escapeHtml(name) + '</option>';
        }).join(''));

        $('#dashboardStatus').html('<option value="all">All statuses</option>' + statuses.map(function (name) {
            return '<option value="' + escapeHtml(name) + '">' + escapeHtml(name) + '</option>';
        }).join(''));

        $('#dashboardAssignee').val(assigneeValue);
        $('#dashboardStatus').val(statusValue);
    }

    function renderDashboard() {
        var filtered = getFilteredIssues();
        renderStats(filtered);
        buildStatusChart(filtered);
        buildWorkloadChart(filtered);
        buildPriorityChart(filtered);
        renderTypes(filtered);
        renderRecentTasks(filtered);
        renderAttention(filtered);
        renderTeamSnapshot(filtered);
        renderGeneratedAt();
    }

    function showLoading(show) {
        $('.dashboard-loading')[show ? 'addClass' : 'removeClass']('active');
    }

    function fetchDashboard(forceRefresh) {
        var now = Date.now();
        var cacheData = localStorage.getItem(CACHE_KEY);
        var cacheTime = localStorage.getItem(CACHE_TIME_KEY);

        if (!forceRefresh && cacheData && cacheTime && (now - parseInt(cacheTime, 10) < CACHE_EXPIRE)) {
            dashboardData = JSON.parse(cacheData);
            renderFilters();
            renderDashboard();
            return;
        }

        showLoading(true);

        $.ajax({
            url: '/api/dashboard',
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                dashboardData = res;
                localStorage.setItem(CACHE_KEY, JSON.stringify(res));
                localStorage.setItem(CACHE_TIME_KEY, now);
                renderFilters();
                renderDashboard();
            },
            error: function (xhr) {
                alert('Failed to load dashboard data. ' + (xhr.responseText || ''));
            },
            complete: function () {
                showLoading(false);
            }
        });
    }

    function clearDashboardCache(callback) {
        showLoading(true);
        $.ajax({
            url: '/api/dashboard/clear',
            method: 'GET',
            dataType: 'json',
            complete: function () {
                localStorage.removeItem(CACHE_KEY);
                localStorage.removeItem(CACHE_TIME_KEY);
                showLoading(false);
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }

    $('#dashboardSearch').on('input', renderDashboard);
    $('#dashboardAssignee, #dashboardStatus, #dashboardRange').on('change', renderDashboard);
    $('#dashboardRefresh, #dashboardRefreshSecondary').on('click', function () {
        clearDashboardCache(function () {
            fetchDashboard(true);
        });
    });
    $('#dashboardClearCache').on('click', function () {
        clearDashboardCache(function () {
            alert('Dashboard cache cleared.');
        });
    });

    fetchDashboard(false);





});

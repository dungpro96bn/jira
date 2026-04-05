(function ($) {
    var state = {
        all: [],
        filtered: [],
        currentPage: 1,
        perPage: 20
    };

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function formatDate(value) {
        if (!value) return '—';
        var date = new Date(value);
        if (isNaN(date.getTime())) return '—';
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function getTypeMarkup(item) {
        var iconUrl = item.issueType && item.issueType.iconUrl ? item.issueType.iconUrl : '';
        var name = item.issueType && item.issueType.name ? item.issueType.name : 'Task';
        var icon = iconUrl
            ? '<img class="archived-type-icon" src="' + escapeHtml(iconUrl) + '" alt="' + escapeHtml(name) + '">' 
            : '<span class="archived-type-fallback"><i class="fa-regular fa-square-check"></i></span>';
        return '<div class="archived-type-wrap">' + icon + '<span>' + escapeHtml(name) + '</span></div>';
    }

    function getPersonHtml(person, fallbackName) {
        var name = person && person.name ? person.name : fallbackName;
        var avatar = person && person.avatar ? person.avatar : '';

        if (avatar) {
            return '<div class="archived-person">' +
                '<img class="archived-person-avatar" src="' + escapeHtml(avatar) + '" alt="' + escapeHtml(name) + '">' +
                '<span>' + escapeHtml(name) + '</span>' +
            '</div>';
        }

        var initial = (name || 'U').charAt(0).toUpperCase();
        return '<div class="archived-person">' +
            '<span class="archived-person-fallback">' + escapeHtml(initial) + '</span>' +
            '<span>' + escapeHtml(name) + '</span>' +
        '</div>';
    }

    function renderSummary(items) {
        var total = items.length;
        var tasks = items.filter(function (item) {
            return !(item.issueType && item.issueType.subtask);
        }).length;
        var subtasks = items.filter(function (item) {
            return !!(item.issueType && item.issueType.subtask);
        }).length;
        var reporters = {};
        var archivers = {};

        items.forEach(function (item) {
            var reporter = item.reporter && item.reporter.name ? item.reporter.name : 'Unknown';
            var archivedBy = item.archivedBy && item.archivedBy.name ? item.archivedBy.name : 'Unknown';
            reporters[reporter] = true;
            archivers[archivedBy] = true;
        });

        var cards = [
            { label: 'Total archived', value: total, meta: 'Stored in archive list', tone: 'tone-blue', icon: '<i class="fa-regular fa-box-archive"></i>' },
            { label: 'Tasks', value: tasks, meta: 'Parent issues', tone: 'tone-green', icon: '<i class="fa-regular fa-square-check"></i>' },
            { label: 'Subtasks', value: subtasks, meta: 'Child work items', tone: 'tone-purple', icon: '<i class="fa-solid fa-diagram-subtask"></i>' },
            { label: 'Reporters', value: Object.keys(reporters).length, meta: 'Unique reporters', tone: 'tone-cyan', icon: '<i class="fa-regular fa-user"></i>' },
            { label: 'Archived by', value: Object.keys(archivers).length, meta: 'Unique archivers', tone: 'tone-amber', icon: '<i class="fa-solid fa-user-shield"></i>' }
        ];

        var html = cards.map(function (card) {
            return '<article class="panel stat-card ' + card.tone + '">' +
                '<div class="stat-top"><div class="stat-label">' + card.label + '</div><div class="stat-icon">' + card.icon + '</div></div>' +
                '<div class="stat-value">' + card.value + '</div>' +
                '<div class="stat-foot">' + card.meta + '</div>' +
            '</article>';
        }).join('');

        $('#archivedSummary').html(html);
        $('#archivedCountChip').text(total + ' items');
    }

    function renderRows(items) {
        var rows = items.map(function (item) {
            return '<tr>' +
                '<td>' + getTypeMarkup(item) + '</td>' +
                '<td><strong class="archived-key">' + escapeHtml(item.key || '—') + '</strong></td>' +
                '<td><div class="archived-summary-cell">' + escapeHtml(item.summary || '—') + '</div></td>' +
                '<td>' + getPersonHtml(item.reporter, 'Unknown') + '</td>' +
                '<td><span class="archived-date-pill">' + escapeHtml(formatDate(item.archivedDate)) + '</span></td>' +
                '<td>' + getPersonHtml(item.archivedBy, 'Unknown') + '</td>' +
            '</tr>';
        }).join('');

        $('#archivedTableBody').html(rows);
    }

    function renderMobile(items) {
        var html = items.map(function (item) {
            return '<article class="archived-mobile-card">' +
                '<div class="archived-mobile-head">' + getTypeMarkup(item) + '<strong class="archived-key">' + escapeHtml(item.key || '—') + '</strong></div>' +
                '<h4>' + escapeHtml(item.summary || '—') + '</h4>' +
                '<div class="archived-mobile-meta"><span>Reporter</span>' + getPersonHtml(item.reporter, 'Unknown') + '</div>' +
                '<div class="archived-mobile-meta"><span>Date archived</span><strong>' + escapeHtml(formatDate(item.archivedDate)) + '</strong></div>' +
                '<div class="archived-mobile-meta"><span>Archived by</span>' + getPersonHtml(item.archivedBy, 'Unknown') + '</div>' +
            '</article>';
        }).join('');

        $('#archivedMobileList').html(html);
    }

    function renderPagination(totalPages) {
        if (totalPages <= 1) {
            $('#archivedPagination').html('');
            return;
        }

        var html = '<button type="button" class="archived-page-btn" data-page="prev"><i class="fa-solid fa-chevron-left"></i></button>';
        for (var i = 1; i <= totalPages; i++) {
            html += '<button type="button" class="archived-page-btn ' + (i === state.currentPage ? 'is-active' : '') + '" data-page="' + i + '">' + i + '</button>';
        }
        html += '<button type="button" class="archived-page-btn" data-page="next"><i class="fa-solid fa-chevron-right"></i></button>';
        $('#archivedPagination').html(html);
    }

    function renderList() {
        var total = state.filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / state.perPage));
        if (state.currentPage > totalPages) {
            state.currentPage = totalPages;
        }
        var start = (state.currentPage - 1) * state.perPage;
        var pageItems = state.filtered.slice(start, start + state.perPage);

        $('#archivedEmptyState').toggle(total === 0);
        $('.archived-table-scroller, #archivedMobileList').toggle(total > 0);

        renderRows(pageItems);
        renderMobile(pageItems);
        renderPagination(totalPages);
    }

    function applyFilters() {
        var keyword = ($('#archivedSearch').val() || '').toLowerCase().trim();
        state.filtered = state.all.filter(function (item) {
            if (!keyword) return true;
            var haystack = [
                item.key || '',
                item.summary || '',
                item.reporter && item.reporter.name ? item.reporter.name : '',
                item.archivedBy && item.archivedBy.name ? item.archivedBy.name : '',
                item.issueType && item.issueType.name ? item.issueType.name : ''
            ].join(' ').toLowerCase();
            return haystack.indexOf(keyword) !== -1;
        });

        state.currentPage = 1;
        renderSummary(state.filtered);
        renderList();
    }

    function sortItems(items) {
        return items.sort(function (a, b) {
            return new Date(b.archivedDate || 0) - new Date(a.archivedDate || 0);
        });
    }

    function loadItems() {
        $('.dashboard-loading').addClass('active');
        $.get(window.archivedWorkItemsConfig.listUrl, function (res) {
            state.all = sortItems(res.items || []);
            applyFilters();
        }).fail(function () {
            state.all = [];
            applyFilters();
        }).always(function () {
            $('.dashboard-loading').removeClass('active');
        });
    }

    $(document).on('input', '#archivedSearch', applyFilters);
    $(document).on('change', '#archivedLimit', function () {
        state.perPage = parseInt($(this).val(), 10) || 20;
        state.currentPage = 1;
        renderList();
    });
    $(document).on('click', '#archivedRefresh', loadItems);
    $(document).on('click', '.archived-page-btn', function () {
        var action = $(this).data('page');
        var totalPages = Math.max(1, Math.ceil(state.filtered.length / state.perPage));
        if (action === 'prev') {
            state.currentPage = Math.max(1, state.currentPage - 1);
        } else if (action === 'next') {
            state.currentPage = Math.min(totalPages, state.currentPage + 1);
        } else {
            state.currentPage = parseInt(action, 10) || 1;
        }
        renderList();
    });

    loadItems();
})(jQuery);

(function ($) {
    var allAttachments = [];

    function formatBytes(bytes) {
        bytes = parseInt(bytes || 0, 10);
        if (bytes <= 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        i = Math.min(i, units.length - 1);
        return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function formatDate(date) {
        if (!date) return 'Unknown date';
        var d = new Date(date);
        if (isNaN(d.getTime())) return date;
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function getCategory(item) {
        var ext = (item.extension || '').toLowerCase();
        var mime = item.mimeType || '';
        if (item.isImage || mime.indexOf('image/') === 0) return 'image';
        if (['zip', 'rar', '7z', 'tar', 'gz'].indexOf(ext) !== -1) return 'archive';
        if (mime.indexOf('pdf') !== -1 || ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt', 'rtf'].indexOf(ext) !== -1) return 'document';
        return 'other';
    }

    function getIconClass(item) {
        var category = getCategory(item);
        if (category === 'image') return 'fa-regular fa-image';
        if (category === 'archive') return 'fa-regular fa-file-zipper';
        if (category === 'document') return 'fa-regular fa-file-lines';
        return 'fa-regular fa-file-code';
    }

    function getFilteredAttachments() {
        var keyword = ($('#attachmentsSearch').val() || '').trim().toLowerCase();
        var type = $('#attachmentsTypeFilter').val() || 'all';

        return allAttachments.filter(function (item) {
            var text = [item.filename, item.issueKey, item.issueSummary, item.author].join(' ').toLowerCase();
            var keywordMatch = !keyword || text.indexOf(keyword) !== -1;
            var typeMatch = type === 'all' || getCategory(item) === type;
            return keywordMatch && typeMatch;
        });
    }

    function renderSummary(items) {
        var total = items.length;
        var images = items.filter(function (item) { return getCategory(item) === 'image'; }).length;
        var documents = items.filter(function (item) { return getCategory(item) === 'document'; }).length;
        var archives = items.filter(function (item) { return getCategory(item) === 'archive'; }).length;
        var totalSize = items.reduce(function (sum, item) { return sum + parseInt(item.size || 0, 10); }, 0);

        var cards = [
            { label: 'All files', value: total, meta: 'Across all visible Jira tasks', tone: 'tone-blue', icon: 'fa-regular fa-folder-open' },
            { label: 'Images', value: images, meta: 'Preview-ready media files', tone: 'tone-green', icon: 'fa-regular fa-image' },
            { label: 'Documents', value: documents, meta: 'Docs, PDFs, sheets, and more', tone: 'tone-purple', icon: 'fa-regular fa-file-lines' },
            { label: 'Library size', value: formatBytes(totalSize), meta: 'Based on filtered results', tone: 'tone-amber', icon: 'fa-solid fa-database' }
        ];

        $('#attachmentsSummary').html(cards.map(function (card) {
            return '<article class="panel stat-card ' + card.tone + '">' +
                '<div class="stat-top"><div class="stat-label">' + card.label + '</div><div class="stat-icon"><i class="' + card.icon + '"></i></div></div>' +
                '<div class="stat-value stat-value-compact">' + card.value + '</div>' +
                '<div class="stat-foot">' + card.meta + '</div>' +
                '</article>';
        }).join(''));
        $('#attachmentsCountChip').text(total + ' file' + (total === 1 ? '' : 's'));
    }

    function renderGrid(items) {
        var html = items.map(function (item) {
            var preview = item.isImage && item.preview
                ? '<img src="' + item.preview + '" alt="' + $('<div>').text(item.filename).html() + '">'
                : '<div class="attachment-file-fallback"><i class="' + getIconClass(item) + '"></i><span>' + ((item.extension || 'FILE').toUpperCase()) + '</span></div>';

            var issueText = $('<div>').text(item.issueKey + ' · ' + item.issueSummary).html();
            var fileText = $('<div>').text(item.filename).html();
            var authorText = $('<div>').text(item.author || 'Unknown').html();
            var authorAvatar = item.authorAvatar ? $('<div>').text(item.authorAvatar).html() : '';
            var downloadUrl = '/attachment-proxy?id=' + encodeURIComponent(item.id) + '&name=' + encodeURIComponent(item.filename) + '&download=1';
            var viewUrl = '/attachment-proxy?id=' + encodeURIComponent(item.id) + '&name=' + encodeURIComponent(item.filename);

            var authorHtml = authorAvatar
                ? '<div class="attachment-card__author">' +
                '<img class="attachment-card__avatar" src="' + authorAvatar + '" alt="' + authorText + '">' +
                '<span>' + authorText + '</span>' +
                '</div>'
                : '<div class="attachment-card__author">' +
                '<i class="fa-regular fa-user"></i><span>' + authorText + '</span>' +
                '</div>';

            return '<article class="attachment-card" data-id="' + item.id + '">' +
                '<a class="attachment-card__preview" href="' + viewUrl + '" target="_blank" rel="noopener">' + preview + '</a>' +
                '<div class="attachment-card__overlay">' +
                '<a class="attachment-card__action" href="' + downloadUrl + '" title="Download"><i class="fa-solid fa-download"></i></a>' +
                '<button type="button" class="attachment-card__action attachment-delete" data-id="' + item.id + '" data-name="' + fileText + '" title="Delete"><i class="fa-regular fa-trash-can"></i></button>' +
                '</div>' +
                '<div class="attachment-card__body">' +
                '<h4 title="' + fileText + '">' + fileText + '</h4>' +
                '<p class="attachment-card__issue" title="' + issueText + '">' + issueText + '</p>' +
                '<div class="attachment-card__meta"><span>' + formatBytes(item.size) + '</span><span>' + formatDate(item.created) + '</span></div>' +
                authorHtml +
                '</div>' +
                '</article>';
        }).join('');

        $('#attachmentsGrid').html(html);
        $('#attachmentsEmptyState').toggle(items.length === 0);
    }

    function renderAll() {
        var filtered = getFilteredAttachments();
        renderSummary(filtered);
        renderGrid(filtered);
    }

    function loadAttachments() {
        $('.dashboard-loading, .checkLoad').addClass('active');
        $.get('/api/attachments', function (res) {
            if (!res || !res.success) {
                alert('Failed to load attachments.');
                return;
            }
            allAttachments = res.attachments || [];
            renderAll();
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to load attachments.');
        }).always(function () {
            $('.dashboard-loading, .checkLoad').removeClass('active');
        });
    }

    $(document).on('input', '#attachmentsSearch', renderAll);
    $(document).on('change', '#attachmentsTypeFilter', renderAll);
    $(document).on('click', '#attachmentsRefresh', loadAttachments);

    $(document).on('click', '.attachment-delete', function () {
        var attachmentId = $(this).data('id');
        var filename = $(this).data('name') || 'this file';

        if (!confirm('Delete ' + filename + ' from Jira?')) {
            return;
        }

        $.post('/api/attachments/delete', { attachmentId: attachmentId }, function (res) {
            if (!res || !res.success) {
                alert((res && res.message) ? res.message : 'Delete failed.');
                return;
            }

            allAttachments = allAttachments.filter(function (item) {
                return String(item.id) !== String(attachmentId);
            });
            renderAll();
        }, 'json').fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed.');
        });
    });

    $(function () {
        if (!$('.attachments-main').length) return;
        loadAttachments();
    });
})(jQuery);

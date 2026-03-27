jQuery(function ($) {

    // Click chọn user

    $(document).on('click', '.assignee-dropdown .user-option', function () {

        debugger;

        let container = $(this).closest('[data-issue-key]');
        let issueKey = container.data('issueKey');

        if (!issueKey) {
            console.error('❌ Missing issueKey');
            return;
        }

        let accountId = $(this).data('id');

        let wrapper = $(this).closest('.assignee-wrapper');

        let name = $(this).find('.name').text();
        let img = $(this).find('img').attr('src');

        // API
        $.post('/api/board/assign', {
            issueKey: issueKey,
            accountId: accountId
        });

        // UI update
        wrapper.find('.assignee-selected .name').text(name);

        if (img) {
            wrapper.find('.assignee-selected img').attr('src', img);

            container.find('.key-assignee .assignee img').attr('src', img);
        } else {
            wrapper.find('.assignee-selected img').attr('src', '/assets/images/default-avatar.jpg');
        }

        $(".open-task-child").each(function (){
           var dataKey = $(this).data('issue-key');
           if(dataKey == issueKey){
               $(this).find('.status-assignee .assignee img').attr('src', img);
           }
        });

        // close dropdown
        wrapper.find('.assignee-dropdown').addClass('hidden');
        $('.assignee-wrapper').removeClass('is-open');
    });


    // Toggle dropdown
    $(document).on('click', '.assignee-selected', function (e) {
        e.stopPropagation();

        let dropdown = $(this).next('.assignee-dropdown');

        $('.assignee-dropdown').not(dropdown).addClass('hidden'); // đóng cái khác
        dropdown.toggleClass('hidden');
        $('.assignee-wrapper').toggleClass('is-open');
    });


    // Click ngoài để đóng
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.assignee-wrapper').length) {
            $('.assignee-dropdown').addClass('hidden');
            $('.assignee-wrapper').removeClass('is-open');
        }
    });

});
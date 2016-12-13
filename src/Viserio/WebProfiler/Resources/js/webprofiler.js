Zepto(function($) {
    var openPanel = false;

    // Symfony VarDumper: Close the by default expanded objects
    $('.sf-dump-expanded')
        .removeClass('sf-dump-expanded')
        .addClass('sf-dump-compact');

    $('.sf-dump-toggle span').html('&#9654;');

    // Toggle Panel
    $('.webprofiler .webprofiler-header .webprofiler-menus a.webprofiler-menu.webprofiler-menu-has-panel').on('click', function(e) {
        e.preventDefault();

        var body = $('.webprofiler .webprofiler-body');
        var panel = '.' + $(this).attr('data-panel-target-id');
        var input = $(panel + ' input[type=radio]');

        if (openPanel === panel) {
            openPanel = false;

            body.removeClass('active');
            $('.webprofiler .webprofiler-body .webprofiler-panel').removeClass('active');
            // remove checked on all input elements
            input.prop('checked', null);
        } else {
            body.addClass('active');

            if (openPanel !== false) {
                $(openPanel).removeClass('active');
            }

            openPanel = panel;

            $(panel).addClass('active');
            input.first().prop('checked', 'checked');
        }
    });

    // close button
    $('.webprofiler .webprofiler-header .hide-button').on('click', function(e) {
        e.preventDefault();

        openPanel = false;

        $('.webprofiler .webprofiler-body, .webprofiler .webprofiler-body .webprofiler-panel').removeClass('active');
        $('.webprofiler .webprofiler-header').addClass('hide');
        $('.webprofiler .show-button').addClass('active');
    });

    // open button
    $('.webprofiler .show-button').on('click', function(e) {
        e.preventDefault();

        $('.webprofiler .webprofiler-header').removeClass('hide');
        $('.webprofiler .show-button').removeClass('active');
    });
});

Zepto(function($) {
    var openPanel = false;
    var panelBodyClass = '.webprofiler .webprofiler-body';
    var panelClass = panelBodyClass + ' .webprofiler-panel';
    var menuHasPanel = '.webprofiler .webprofiler-header .webprofiler-menus a.webprofiler-menu.webprofiler-menu-has-panel';
    var bodyMenu = panelBodyClass + ' .webprofiler-body-menu';
    var resizeIsActive = false;
    var resizeBodyAndTabContent = function() {
        $(panelBodyClass).height($(window).height() - 38);
        $('.webprofiler-tabs-tab-content').height($(panelBodyClass).height() - 87);
    };

    // Symfony VarDumper: Close the by default expanded objects
    $('.sf-dump-expanded')
        .removeClass('sf-dump-expanded')
        .addClass('sf-dump-compact');

    $('.sf-dump-toggle span').html('&#9654;');

    // Toggle Panel
    $(menuHasPanel).on('click', function(e) {
        e.preventDefault();

        var panel = '.' + $(this).attr('data-panel-target-id');
        var input = $(panel + ' input[type=radio]');

        $(menuHasPanel).removeClass('active');
        $(this).addClass('active');

        if (openPanel === panel) {
            openPanel = false;

            $(menuHasPanel + ', ' + panelBodyClass + ', ' + openPanel).removeClass('active');
            $(panel).removeClass('active');

            // remove checked on all input elements
            input.prop('checked', null);
        } else {
            $(panelBodyClass).addClass('active');

            if (openPanel !== false) {
                $(openPanel).removeClass('active');
            }

            openPanel = panel;

            $(panel).addClass('active');
            input.first().prop('checked', 'checked');
        }
    });

    // close webprofiler-body panel
    $(bodyMenu + ' .webprofiler-body-close-panel').on('click', function(e) {
        $(menuHasPanel + ', ' + panelBodyClass + ', ' + openPanel).removeClass('active');

        openPanel = false;
    });

    $(bodyMenu + ' .webprofiler-body-resize-panel').on('click', function(e) {
        if (resizeIsActive) {
            resizeIsActive = false;

            $(panelBodyClass).height(null);
            $('.webprofiler-tabs-tab-content').height(null);
            $(this).removeClass('orginal-size-panel');
        } else {
            resizeIsActive = true;
            resizeBodyAndTabContent();

            $(this).addClass('orginal-size-panel');
        }
    });

    $(window).resize(function() {
        if (resizeIsActive) {
            resizeBodyAndTabContent();
        }
    });

    // close webprofiler button
    $('.webprofiler .webprofiler-header .webprofiler-hide-button').on('click', function(e) {
        e.preventDefault();

        openPanel = false;

        $('.webprofiler .webprofiler-body, ' + panelClass).removeClass('active');
        $('.webprofiler .webprofiler-header').addClass('hide');
        $('.webprofiler .webprofiler-show-button').addClass('active');
    });

    // open button
    $('.webprofiler .webprofiler-show-button').on('click', function(e) {
        e.preventDefault();

        $('.webprofiler .webprofiler-header').removeClass('hide');
        $('.webprofiler .webprofiler-show-button').removeClass('active');
    });

    // select content
    var selected = $(panelClass + ' .content-selector option').not(function () {
        return !this.selected;
    });

    $('#' + selected.val()).addClass('active');

    $(panelClass + ' .content-selector').on('change', function () {
        var content = $(panelClass + ' .content-selector option').not(function () {
            return !this.selected;
        });

        if ($(panelClass + ' .selected-content').hasClass('active')) {
            $(panelClass + ' .selected-content').removeClass('active');
        }

        $(panelClass + ' #' + content.val()).addClass('active');
    });
});

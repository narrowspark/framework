Zepto(function($) {
    var openPanel = false;
    var panelBodyClass = '.webprofiler .webprofiler-body';
    var panelClass = panelBodyClass + ' .webprofiler-panel';
    var i = 0;
    var dragging = false;

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
            $(panelClass).removeClass('active');
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

        $('.webprofiler .webprofiler-body, ' + panelClass).removeClass('active');
        $('.webprofiler .webprofiler-header').addClass('hide');
        $('.webprofiler .show-button').addClass('active');
    });

    // open button
    $('.webprofiler .show-button').on('click', function(e) {
        e.preventDefault();

        $('.webprofiler .webprofiler-header').removeClass('hide');
        $('.webprofiler .show-button').removeClass('active');
    });

    // select content
    var selected = $(panelClass + ' .content-selector option').not(function () {
        return !this.selected
    });

    $('.' + selected.val()).addClass('active');

    $(panelClass + ' .content-selector').on('change', function () {
        var content = $(panelClass + ' .content-selector option').not(function () {
            return !this.selected
        });

        if ($(panelClass + ' .selected-content').hasClass('active')) {
            $(panelClass + ' .selected-content').removeClass('active');
        }

        $(panelClass + ' .' + content.val()).addClass('active');
    });

    // resize webprofiler body
    var selector = $('#webprofiler-body-dragbar');
    var startY;
    var startHeight;
    var initDrag = function (e) {
        e.preventDefault();

       startY = e.clientY;
       startHeight = parseInt($(panelBodyClass).height(), 10);

       $('body').on('mousemove', doDrag);
       $('body').on('mouseup', stopDrag);
    };
    var doDrag =function (e) {
       $(panelBodyClass).height((startHeight + startY - e.clientY) + 'px');
    };
    var stopDrag = function (e) {
        $('body').off('mousemove', doDrag);
        $('body').off('mouseup', stopDrag);
        $('#webprofiler-body-ghostbar').remove();
    };

    selector.on('mousedown', function init(e) {
        e.preventDefault();

        selector.className = selector.className + ' resizable';

        $('#webprofiler-body-ghostbar').remove();

        var ghostbar = $('<div>', {id:'webprofiler-body-ghostbar'}).appendTo($(panelBodyClass));

        ghostbar.on('mousedown', initDrag);
    });
});

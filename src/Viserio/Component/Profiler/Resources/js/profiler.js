if (typeof(Profiler) == 'undefined') {
    // namespace
    var Profiler = {};

    Profiler.$ = Zepto;
}

(function($) {
    "use strict";

    if (typeof(localStorage) == 'undefined') {
        // provide mock localStorage object for dumb browsers
        localStorage = {
            setItem: function(key, value) {},
            getItem: function(key) { return null; }
        };
    }

    var profilerStorageKey = 'narrowspark/profiler/';

    var openPanel = false;

    // helper vars for long class names
    var panelBodyClass = '.profiler .profiler-body';
    var panelClass = panelBodyClass + ' .profiler-panel';
    var menuHasPanel = '.profiler .profiler-header .profiler-menus .profiler-menu.profiler-menu-has-panel';
    var bodyMenu = panelBodyClass + ' .profiler-body-menu';
    var profilerHeader = '.profiler .profiler-header';
    var getPreference = function(name) {
        if (!window.localStorage) {
            return null;
        }

        return localStorage.getItem(profilerStorageKey + name);
    };
    var setPreference = function(name, value) {
        if (!window.localStorage) {
            return null;
        }

        localStorage.setItem(profilerStorageKey + name, value);
    };
    var resizeBodyAndTabContent = function(panelHeight, tabContentHeight) {
        $(panelBodyClass).height(panelHeight);
        $('.profiler-tabs-tab-content').height(tabContentHeight);

        setPreference('panelHeight', panelHeight);
        setPreference('tabContentHeight', tabContentHeight);
    };

    // Symfony VarDumper: Close the by default expanded objects
    Profiler.symfony = function() {
        $('.sf-dump-expanded')
            .removeClass('sf-dump-expanded')
            .addClass('sf-dump-compact');

        $('.sf-dump-toggle span').html('&#9654;');
    };

    // Toggle Panel
    Profiler.toggle = function() {
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
    };

    Profiler.openCloseHandler = function() {
        // close profiler-body panel
        $(bodyMenu + ' .profiler-body-close-panel').on('click', function(e) {
            $(menuHasPanel + ', ' + panelBodyClass + ', ' + openPanel).removeClass('active');

            openPanel = false;
        });

        // close button
        $('.profiler .profiler-header .profiler-hide-button').on('click', function(e) {
            e.preventDefault();

            openPanel = false;

            $('.profiler .profiler-body, ' + panelClass).removeClass('active');
            $(profilerHeader).addClass('hide');
            $('.profiler .profiler-show-button').addClass('active');
        });

        // open button
        $('.profiler .profiler-show-button').on('click', function(e) {
            e.preventDefault();

            $(profilerHeader).removeClass('hide');
            $('.profiler .profiler-show-button').removeClass('active');
        });
    };

    // select content
    Profiler.selectContent = function() {
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
    };

    Profiler.resize = function() {
        var resizeIsActive = false;

        $(bodyMenu + ' .profiler-body-resize-panel').on('click', function (e) {
            if (resizeIsActive) {
                resizeIsActive = false;

                $(panelBodyClass).height(null);
                $(panelClass + ' .profiler-tabs .profiler-tabs-tab-content').height(null);
                $(this).removeClass('orginal-size-panel');
            } else {
                var panelHeight = $(window).height() - 38;
                var contentHeight = $(window).height() - $(panelClass + ' .profiler-tabs').height() + ($(profilerHeader).height() + $(bodyMenu).height());

                resizeIsActive = true;
                resizeBodyAndTabContent(panelHeight, $(window).height() + $(bodyMenu).height() - $(panelClass + ' .profiler-tabs').height() + $(profilerHeader).height() + 4);

                $(this).addClass('orginal-size-panel');
            }
        });

        $(window).resize(function() {
            if (resizeIsActive) {
                var panelHeight = $(window).height() - 38;
                var contentHeight = $(window).height() - $(panelClass + ' .profiler-tabs').height() + ($(profilerHeader).height() + $(bodyMenu).height());

                resizeBodyAndTabContent(panelHeight, contentHeight);
            }
        });
    };

    Profiler.restoreState = function() {
    };
})(Profiler.$);

Profiler.$(function() {
    Profiler.symfony();
    Profiler.toggle();
    Profiler.openCloseHandler();
    Profiler.selectContent();
    Profiler.resize();
});

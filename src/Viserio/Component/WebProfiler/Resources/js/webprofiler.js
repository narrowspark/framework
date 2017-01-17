if (typeof(WebProfiler) == 'undefined') {
    // namespace
    var WebProfiler = {};

    WebProfiler.$ = Zepto;
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

    var profilerStorageKey = 'ns/profiler/';

    var openPanel = false;

    // helper vars for long class names
    var panelBodyClass = '.webprofiler .webprofiler-body';
    var panelClass = panelBodyClass + ' .webprofiler-panel';
    var menuHasPanel = '.webprofiler .webprofiler-header .webprofiler-menus .webprofiler-menu.webprofiler-menu-has-panel';
    var bodyMenu = panelBodyClass + ' .webprofiler-body-menu';
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
        $('.webprofiler-tabs-tab-content').height(tabContentHeight);

        setPreference('panelHeight', panelHeight);
        setPreference('tabContentHeight', tabContentHeight);
    };

    // Symfony VarDumper: Close the by default expanded objects
    WebProfiler.symfony = function() {
        $('.sf-dump-expanded')
            .removeClass('sf-dump-expanded')
            .addClass('sf-dump-compact');

        $('.sf-dump-toggle span').html('&#9654;');
    };

    // Toggle Panel
    WebProfiler.toggle = function() {
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

    WebProfiler.openCloseHandler = function() {
        // close webprofiler-body panel
        $(bodyMenu + ' .webprofiler-body-close-panel').on('click', function(e) {
            $(menuHasPanel + ', ' + panelBodyClass + ', ' + openPanel).removeClass('active');

            openPanel = false;
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
    };

    // select content
    WebProfiler.selectContent = function() {
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

    WebProfiler.resize = function() {
        var resizeIsActive = false;
        var panelHeight = $(window).height() - 38;
        var tabContentHeight = $(panelBodyClass).height() - 87;

        $(bodyMenu + ' .webprofiler-body-resize-panel').on('click', function(e) {
            if (resizeIsActive) {
                resizeIsActive = false;

                $(panelBodyClass).height(null);
                $('.webprofiler-tabs-tab-content').height(null);
                $(this).removeClass('orginal-size-panel');
            } else {
                resizeIsActive = true;
                resizeBodyAndTabContent(panelHeight, tabContentHeight);

                $(this).addClass('orginal-size-panel');
            }
        });

        $(window).resize(function() {
            if (resizeIsActive) {
                resizeBodyAndTabContent(panelHeight, tabContentHeight);
            }
        });
    };

    WebProfiler.restoreState = function() {
    };
})(WebProfiler.$);

WebProfiler.$(function() {
    WebProfiler.symfony();
    WebProfiler.toggle();
    WebProfiler.openCloseHandler();
    WebProfiler.selectContent();
    WebProfiler.resize();
});

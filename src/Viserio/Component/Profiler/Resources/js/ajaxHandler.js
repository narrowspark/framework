/**
 * AjaxHandler
 *
 * Extract data from headers of an XMLHttpRequest and adds a new dataset
 */
var AjaxHandler = Profiler.AjaxHandler = function(Profiler, headerName) {
    this.profiler = Profiler;
    this.headerName = headerName || 'Profiler';
};

var requestStack = [];
var pendingRequests = 0;

Profiler.$.extend(AjaxHandler.prototype, {
    renderAjaxRequests: function() {
        var ajaxToolbarPanel = $('.profiler-menu-ajax-requests-data-collector');
        var requestCounter = ajaxToolbarPanel.children('.profiler-menu-label');

        if (!requestCounter) {
            return;
        }

        requestCounter.textContent = requestStack.length;

        var infoSpan = ajaxToolbarPanel.children('.profiler-menu-tooltip-group-piece b');

        if (infoSpan) {
            infoSpan.textContent = requestStack.length + ' AJAX request' + (requestStack.length !== 1 ? 's' : '');
        }

        if (requestStack.length) {
            ajaxToolbarPanel.style.display = 'block';
        } else {
            ajaxToolbarPanel.style.display = 'none';
        }

        if (pendingRequests > 0) {
            ajaxToolbarPanel.addClass('sf-ajax-request-loading');
        } else if (successStreak < 4) {
            ajaxToolbarPanel.addClass('sf-toolbar-status-red');
            ajaxToolbarPanel.removeClass('sf-ajax-request-loading');
        } else {
            ajaxToolbarPanel.removeClass('sf-ajax-request-loading');
            ajaxToolbarPanel.removeClass('sf-toolbar-status-red');
        }
    },

    extractHeaders: function(xhr, stackElement) {
        /* Here we avoid to call xhr.getResponseHeader in order to */
        /* prevent polluting the console with CORS security errors */
        var allHeaders = xhr.getAllResponseHeaders();
        var ret;

        if (ret = allHeaders.match(/^x-debug-token:\s+(.*)$/im)) {
            stackElement.profile = ret[1];
        }

        if (ret = allHeaders.match(/^x-debug-token-link:\s+(.*)$/im)) {
            stackElement.profilerUrl = ret[1];
        }
    },

    bindToZepto: function (zepto) {
        var self = this;
        var document = zepto(document);

        document.on('ajaxComplete', function (e, xhr, options) {
            pendingRequests--;
        }).on('ajaxSend', function () {
            pendingRequests++;
        })
    }
});

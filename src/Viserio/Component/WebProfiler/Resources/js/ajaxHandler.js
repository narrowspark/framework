/**
 * AjaxHandler
 *
 * Extract data from headers of an XMLHttpRequest and adds a new dataset
 */
var AjaxHandler = WebProfiler.AjaxHandler = function(webprofiler, headerName) {
    this.webprofiler = webprofiler;
    this.headerName = headerName || 'webprofiler';
};

WebProfiler.$.extend(AjaxHandler.prototype, {

});

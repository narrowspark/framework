/**
 * AjaxHandler
 *
 * Extract data from headers of an XMLHttpRequest and adds a new dataset
 */
var AjaxHandler = Profiler.AjaxHandler = function(Profiler, headerName) {
    this.profiler = Profiler;
    this.headerName = headerName || 'Profiler';
};

Profiler.$.extend(AjaxHandler.prototype, {

});

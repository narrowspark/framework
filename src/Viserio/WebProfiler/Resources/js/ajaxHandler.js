$.extend(AjaxHandler.prototype, {

    /**
     * Handles an XMLHttpRequest
     *
     * @this {AjaxHandler}
     * @param {XMLHttpRequest} xhr
     * @return {Bool}
     */
    handle: function(xhr) {
         // Check if the debugbar header is available
        if (xhr.getAllResponseHeaders().indexOf(this.headerName) === -1){
            return true;
        }

        if (!this.loadFromId(xhr)) {
            return this.loadFromData(xhr);
        }

        return true;
    },

    /**
     * Checks if the HEADER-id exists and loads the dataset using the open handler
     *
     * @param {XMLHttpRequest} xhr
     * @return {Bool}
     */
    loadFromId: function(xhr) {
        var id = this.extractIdFromHeaders(xhr);

        if (id && this.debugbar.openHandler) {
            this.debugbar.loadDataSet(id, "(ajax)");
            return true;
        }

        return false;
    },

    /**
     * Extracts the id from the HEADER-id
     *
     * @param {XMLHttpRequest} xhr
     * @return {String}
     */
    extractIdFromHeaders: function(xhr) {
        return xhr.getResponseHeader(this.headerName + '-id');
    },

    /**
     * Checks if the HEADER exists and loads the dataset
     *
     * @param {XMLHttpRequest} xhr
     * @return {Bool}
     */
    loadFromData: function(xhr) {
        var raw = this.extractDataFromHeaders(xhr);
        if (!raw) {
            return false;
        }

        var data = this.parseHeaders(raw);

        if (data.error) {
            throw new Error('Error loading debugbar data: ' + data.error);
        } else if(data.data) {
            this.debugbar.addDataSet(data.data, data.id, "(ajax)");
        }

        return true;
    },

    /**
     * Extract the data as a string from headers of an XMLHttpRequest
     *
     * @this {AjaxHandler}
     * @param {XMLHttpRequest} xhr
     * @return {string}
     */
    extractDataFromHeaders: function(xhr) {
        var data = xhr.getResponseHeader(this.headerName);

        if (!data) {
            return;
        }

        for (var i = 1;; i++) {
            var header = xhr.getResponseHeader(this.headerName + '-' + i);

            if (!header) {
                break;
            }

            data += header;
        }

        return decodeURIComponent(data);
    },

    /**
     * Parses the string data into an object
     *
     * @this {AjaxHandler}
     * @param {string} data
     * @return {string}
     */
    parseHeaders: function(data) {
        return JSON.parse(data);
    },

    /**
     * Attaches an event listener to $.ajax().ajaxComplete()
     *
     * @this {AjaxHandler}
     * @param {zepto} zepto Optional
     */
    bindToZepto: function(zepto) {
        var self = this;

        zepto(document).on('ajaxComplete', function(e, xhr, settings) {
            if (!settings.ignoreDebugBarAjaxHandler) {
                self.handle(xhr);
            }
        });
    },

    /**
     * Attaches an event listener to XMLHttpRequest
     *
     * @this {AjaxHandler}
     */
    bindToXHR: function() {
        var self = this;
        var proxied = XMLHttpRequest.prototype.open;

        XMLHttpRequest.prototype.open = function(method, url, async, user, pass) {
            var xhr = this;

            this.addEventListener("readystatechange", function() {
                var skipUrl = self.debugbar.openHandler ? self.debugbar.openHandler.get('url') : null;

                if (xhr.readyState == 4 && url.indexOf(skipUrl) !== 0) {
                    self.handle(xhr);
                }
            }, false);

            proxied.apply(this, Array.prototype.slice.call(arguments));
        };
    }
});

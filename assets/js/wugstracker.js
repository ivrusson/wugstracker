/**
 * @name WugsTracker
 * 
 * Javascript function to catch errors and send to the server to store the data.
 * 
 * @author ivrusson <ivrusson@maquinantes.com>
 * 
 * @version 1.0.0
 * 
 */
(function() {
    function WugsTracker(options = {}) {
        var defaultOptions = {
            cachePersistency: 15,
            trackConsoleErrors: false,
        };

        this.options = Object.assign(defaultOptions, options);
        this.cache = [];

        this.init();
    }

    WugsTracker.prototype.init = function () {
        var _this = this;
        //Defining new console for caching events  
        var console = (function (prev) {
            return {
                log: function (str) {
                    _this.cached('console.log', str);
                    prev.log(str);
                },
                info: function (str) {
                    _this.cached('console.info', str);
                    prev.info(str);
                },
                warn: function (str) {
                    _this.cached('console.warn', str);
                    prev.warn(str);
                },
                error: function (str) {
                    _this.cached('console.error', str);
                    if (_this.options.trackConsoleErrors) {
                        var err = new Error('Error from trackConsoleErrors feature');
                        _this.captureError({ msg: str, url: window.location.href, lineNo: err.lineNumber, columnNo: null, error: err });
                    }
                    prev.error(str);
                }
            };
        }(window.console));

        //Override the console
        window.console = console;
        //Init window events
        this.initEvents();
    };

    WugsTracker.prototype.initEvents = function () {
        var _this = this;
        window.onerror = function (msg, url, lineNo, columnNo, error) {
            _this.captureError({ msg, url, lineNo, columnNo, error });
        };
    }

    WugsTracker.prototype.cached = function (type, str) {
        var cachePersistency = this.options.cachePersistency;
        if (this.cache.length > cachePersistency) {
            var count = this.cache.length - cachePersistency;
            this.cache = this.cache.slice(this.cache.length - count, count);
        }

        this.cache.push({
            mgs: type + ': ' + str,
            time: new Date()
        });
    };

    WugsTracker.prototype.captureError = function (errorData) {
        var extraData = {
            source: 'js',
            type: 'error',
            site_url: window.location.href,
            console: this.cache
        };
        var errorData = Object.assign(extraData, errorData);
        var data = JSON.stringify(errorData);

        var xhr = new XMLHttpRequest();
        xhr.withCredentials = true;

        xhr.addEventListener("readystatechange", function () {
            if (this.readyState === 4) {
                console.log(this.responseText);
            }
        });

        xhr.open("POST", wugsData.api_endpoint);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader("X-Wp-Js-Debugger-Nonce", this.getCookie('wugstracker-nonce'));

        xhr.send(data);
    };

    WugsTracker.prototype.getCookie = function (name) {
        var str = RegExp(name + "=[^;]+").exec(document.cookie);

        return decodeURIComponent(!!str ? str.toString().replace(/^[^=]+./, "") : "");
    };

    window.jsDebugger = new WugsTracker();
})();
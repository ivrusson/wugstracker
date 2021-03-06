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
function WugsTracker(options = {}) {
    var defaultOptions = {
        cachePersistency: 15,
        trackConsoleErrors: false,
    };

    this.options = Object.assign(defaultOptions, options);
    this.cache = [];

    this.init();
}

WugsTracker.prototype.init = function() {
    var _this = this;
    //Defining new console for caching events  
    var console = (function(prev) {
        return {
            log: function(str) {
                prev.log(str);
                _this.cached('JS LOG', str, new Date());
            },
            info: function(str) {
                prev.info(str);
                _this.cached('JS INFO', str, new Date());
            },
            warn: function(str) {
                prev.warn(str);
                _this.cached('JS WARN', str, new Date());
            },
            error: function(str) {
                prev.error(str);
                _this.cached('JS ERROR', str, new Date());
                if (_this.options.trackConsoleErrors) {
                    var err = new Error('Error from trackConsoleErrors feature');
                    _this.captureError({ msg: str, url: window.location.href, lineNo: err.lineNumber, columnNo: null, error: err });
                }
            }
        };
    }(window.console));

    //Override the console
    window.console = console;
};

WugsTracker.prototype.cached = function (type, str, date) {
    var cachePersistency = this.options.cachePersistency;
    if (this.cache.length > cachePersistency) {
        var count = this.cache.length - cachePersistency;
        this.cache = this.cache.slice(this.cache.length - count, count);
    }

    this.cache.push({
        mgs: type + ': ' + str,
        time: date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear() + " " + date.getHours() + ":" + date.getMinutes()
    });
};

WugsTracker.prototype.captureError = function (errorData) {
    var extraData = {
        source: 'js',
        type: 'error',
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

WugsTracker.prototype.getCookie = function(name) {
    var str = RegExp(name + "=[^;]+").exec(document.cookie);

    return decodeURIComponent(!!str ? str.toString().replace(/^[^=]+./, "") : "");
};

window.onerror = function (msg, url, lineNo, columnNo, error) {
    var jsDebugger = new WugsTracker();

    jsDebugger.captureError({ msg, url, lineNo, columnNo, error});
};


console.log('Prueba de console 1')
console.log('Prueba de console 2')
console.log('Prueba de console 3')
notThere();
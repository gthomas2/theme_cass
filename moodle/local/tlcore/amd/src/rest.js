define(["jquery", "core/str", "core/notification"], function($, CoreStr, notification) {
    var RestClass = function(restUrl) {
        this.restUrl = restUrl;

        /**
         * Get strings relevant to this class.
         * @returns {Promise<T>}
         */
        var getStrings = function() {
            return CoreStr.get_strings([
                {key: 'ok'},
                {key: 'cancel'},
                {key: 'login'},
                {key: 'error'},
                {key: 'resturlnotset', component: 'local_tlcore'},
                {key: 'notloggedinmsg', component: 'local_tlcore'},
                {key: 'notloggedin', component: 'local_tlcore'}
            ])
                .then (function(strings) {
                    var keyStrings = {};
                    keyStrings.ok = strings[0];
                    keyStrings.cancel = strings[1];
                    keyStrings.login = strings[2];
                    keyStrings.error = strings[3];
                    keyStrings.resturlnotset = strings[4];
                    keyStrings.notloggedinmsg = strings[5];
                    keyStrings.notloggedin = strings[6];
                    var dfd = $.Deferred();
                    dfd.resolve(keyStrings);
                    return dfd;
                });
        };

        /**
         *
         * @param string action
         * @param object data
         * @param string method
         * @param function ? customErrorHandler
         * @param ajaxOverlay ajaxOverlay
         * @returns {deferred|boolean}
         */
        this.call = function(action, data, method, customErrorHandler, ajaxOverlay) {
            return getStrings()
                .then((strings) => {
                    if (!this.restUrl) {
                        notification.alert(strings.error, strings.resturlnotset);
                    }
                    if (!data) {
                        data = {};
                    }
                    data.action = action;

                    var notLoggedInError = function() {
                        var logInLink = M.cfg.wwwroot + '/login';
                        var msg = `${strings.notloggedinmsg}
                            <div class="mt-4">
                                <a target="_blank" href="${logInLink}">${strings.login}</a>
                            </div>`;
                        notification.alert(strings.notloggedin,
                            msg, strings.ok);
                    };

                    var onErrorGeneral = function(jqXHR) {

                        if (ajaxOverlay) {
                            ajaxOverlay.removeOverlay();
                        }

                        // Note - don't bother localising error messages.
                        if (!jqXHR) {
                            jqXHR = {status: 'jqXHR object invalid', responseText: ''};
                        }

                        window.console.error('error - jqXHR', jqXHR);

                        var error, errorcode, stacktrace;

                        if (!jqXHR.responseJSON) {
                            error = 'Unknown error';
                            errorcode = 'unknown';
                            stacktrace = 'unknown - possible bad JSON? ' + jqXHR.responseText;
                        } else {
                            error = jqXHR.responseJSON.error;
                            errorcode = jqXHR.responseJSON.errorcode;
                            stacktrace = jqXHR.responseJSON.stacktrace;
                        }

                        if (jqXHR.responseJSON &&
                            jqXHR.responseJSON.errorcode &&
                            jqXHR.responseJSON.errorcode === 'requireloginerror') {
                            return notLoggedInError();
                        }

                        var msg = '<div class="ajaxErrors">'
                            + '<div class="ajaxErrorMsg">' + error + '</div>'
                            + '<div class="ajaxErrorStatus">Error status code: ' + jqXHR.status + '</div>'
                            + '<div class="ajaxErrorCode">Error code: ' + errorcode + '</div>'
                            + '<div class="ajaxErrorStackTrace">Stack trace: ' + stacktrace + '</div>'
                            + '</div>';

                        notification.alert(strings.anerrorhasoccurred,
                            msg, strings.ok);
                    };

                    var errorFunction = function(jqXHR) {
                        if (typeof(customErrorHandler) === 'function') {
                            if (customErrorHandler(jqXHR)) {
                                return;
                            } else {
                                // If the custom error handler doesn't return true then we go onto call the general error handler.
                                onErrorGeneral(jqXHR);
                            }
                        }
                    };

                    return $.ajax({
                        url: this.restUrl,
                        data: data,
                        method: method,
                        error: errorFunction
                    }).then(function(data) {
                        if (ajaxOverlay) {
                            ajaxOverlay.removeOverlay();
                        }
                        return data;
                    });
                });
        };
        this.get = function(action, data, error, ajaxOverlay) {
            return this.call(action, data, 'GET', error, ajaxOverlay);
        };
        this.post = function(action, data, error, ajaxOverlay) {
            return this.call(action, data, 'POST', error, ajaxOverlay);
        };
        this.put = function(action, data, error, ajaxOverlay) {
            return this.call(action, data, 'PUT', error, ajaxOverlay);
        };
        this.patch = function(action, data, error, ajaxOverlay) {
            return this.call(action, data, 'PATCH', error, ajaxOverlay);
        };
        this.delete = function(action, data, error, ajaxOverlay) {
            return this.call(action, data, 'DELETE', error, ajaxOverlay);
        };
        this.downloadDirect = function(action, data) {
            return getStrings()
                .then((strings) => {
                    if (!this.restUrl) {
                        notification.alert(strings.error, strings.resturlnotset);
                    }
                    var link = document.createElement('a');

                    // Convert data object to url params.
                    var queryString = !data ? '' :
                        Object.keys(data).map(key => key + '=' + encodeURIComponent(data[key])).join('&');

                    link.setAttribute('href', restUrl+'?action='+action+'&'+queryString);
                    link.setAttribute('download', data.filename);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
        };
    };

    return {
        newAPI: function(restUrl) {
            return new RestClass(restUrl);
        }
    };
});
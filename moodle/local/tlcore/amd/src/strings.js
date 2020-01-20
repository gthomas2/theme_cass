define(
    ['jquery', 'core/config', 'core/str', 'core/notification', 'local_tlcore/rest', 'core/localstorage'],
    function($, Config, Str, Notification, Rest, Storage) {
        var component_strings_requests = {};
        return {
            /**
             * Wrapper for core method.
             * @param key
             * @param component
             * @param param
             * @param lang
             * @returns {*|Promise}
             */
            get_string: function(key, component, param, lang) {
                return Str.get_string(key, component, param, lang);
            },

            /**
             * get_strings - better than core.
             * It will return an object with properties as key names of strings.
             * @param array keyComps - {key: key, component: component, param: param, lang: lang}
             * @param bool keyByComponent - if true will add component to string key- e.g.
             * 'mycomponent.name' instead of just 'name'
             */
            get_strings: function(keyComps, keyByComponent) {
                var dfd = $.Deferred();

                Str.get_strings(keyComps).done(function(strings) {
                    var stringsByKey = {};
                    for (var s in strings) {
                        var string = strings[s];
                        var keyComp = keyComps[s];
                        if (keyByComponent) {
                            stringsByKey[keyComp.component + '.' + keyComp.key] = string;
                        } else {
                            stringsByKey[keyComp.key] = string;
                        }
                    }
                    dfd.resolve(stringsByKey);
                }).fail(Notification.exception);

                return dfd;
            },

            /**
             * Get all the strings for a component - because why not.
             * @param component
             * @returns {*}
             */
            get_component_strings: function(component) {
                var dfd = $.Deferred();
                var lang = $('html').attr('lang').replace(/-/g, '_');
                var cacheKey = 'local_tlcore/component_strings/'+component+'/'+lang;
                if (component_strings_requests[cacheKey]) {
                    return component_strings_requests[cacheKey];
                }
                component_strings_requests[cacheKey] = dfd;
                var strings = Storage.get(cacheKey);
                if (strings && typeof(strings) !== 'undefined') {
                    strings = JSON.parse(strings);
                    dfd.resolve(strings);
                    return dfd;
                }

                var DataService = Rest.newAPI(Config.wwwroot + '/local/tlcore/rest.php');
                DataService.get('get_component_strings', {component: component})
                    .then(function(r) {
                        var strings = r.result;
                        Storage.set(cacheKey, JSON.stringify(strings));
                        dfd.resolve(strings);
                    });
                return dfd;
            }
        };
    });
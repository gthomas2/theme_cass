define([], function() {
    return {
        /**
         * Bootstrap vuejs with 'mainPlugin' as the directory for loading non tlcore JS.
         * @param string mainPlugin - frankenstyle for the plugin using this - e.g. "mod_tlevent"
         * @param string vueMainCompRqJs - require js component name for main vue component -
         * e.g. "mod_tlevent/vuecomp/venue_management" would simply be "venue_management"
         * @param string vueElSelector - dom element selector for housing vue component  - e.g. "#mod_tlevent_vue"
         * @param string vueCompTag - main tag for vue element component, e.g "venue-management" for <venue-management>
         */
        init: function(mainPlugin, vueMainCompRqJs, vueCompTag, vueElSelector) {
            var jsrev = M.cfg.jsrev;

            /**
             * Return the js url for plugins in tlcore.
             * @param path
             * @returns {string}
             */
            var tlcoreJSURL = function(path) {
                return `${M.cfg.wwwroot}/local/tlcore/javascript.php/${jsrev}/local_tlcore/${path}`;
            };

            /**
             * Return the js url for plugins in your plugin.
             * @param path
             * @returns {string}
             */
            var pluginJSURL = function(path) {
                // NOTE - you should change the plugin frankenstyle to suit your plugin here.
                return `${M.cfg.wwwroot}/local/tlcore/javascript.php/${jsrev}/${mainPlugin}/${path}`;
            };
            require.config({
                enforceDefine: false,
                paths: {
                    // Vendor code.
                    "local_tlcore/vue": [
                        "https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.min",
                        // CDN Fallback - whoop whoop!
                        tlcoreJSURL("vendorjs/vue")
                    ],
                    "local_tlcore/promise": [
                        "https://cdn.jsdelivr.net/npm/promise-polyfill@8/dist/polyfill.min",
                        // CDN Fallback - whoop whoop!
                        tlcoreJSURL("vendorjs/promise")
                    ],
                    // Note, vuedatable is not via a CDN because it has been customised (made more accessible).
                    "local_tlcore/vuedatatable": tlcoreJSURL("vendorjs/vuedatatable"),

                    // Vue components.
                    "local_tlcore/vuecomp": tlcoreJSURL("vue/comps"),
                    "mod_tlevent/vuecomp": pluginJSURL("vue/comps"),
                }
            });

            return require([
                    "local_tlcore/vue",
                    "local_tlcore/vuedatatable",
                    `${mainPlugin}/vuecomp/${vueMainCompRqJs}`
                ],
                function(Vue, VueDataTable, VueMainComp) {

                    var init = function() {
                        var opts = {
                            el: vueElSelector,
                            router: null
                        };
                        // If JS caching is turned off, then turn dev tools for vue on.
                        if (parseInt(M.cfg.jsrev) === -1) {
                            Vue.config.devtools = true;
                        }
                        window.Vue = Vue;
                        Vue.use(VueDataTable.default);
                        Vue.component(vueCompTag, VueMainComp);

                        new Vue(opts).$mount(vueElSelector);
                    };

                    if (typeof(Promise) !== 'undefined') {
                        init();
                    } else {
                        require(['local_tlcore/promise'], function() {
                            init();
                        });
                    }

                });

        }
    };
});
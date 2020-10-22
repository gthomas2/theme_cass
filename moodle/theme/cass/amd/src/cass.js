/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Theme CASS main JS file.
 *
 * @package   theme_cass
 * @author    Guy Thomas <guy.thomas@tituslearning.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'theme_cass/breadcrumb', 'core/templates', 'theme_snap/util'],
    function($, ajax, breadcrumb, Templates, Util) {

    return new function() {

        // Add some aditional vendor requirejs AMD modules.
        // For some reason putting this above the define causes a requirejs error when you have mod_bootstrapelements
        // installed, so it's been moved here.
        (function() {
            var pluginJSURL = function(path) {
                return M.cfg.wwwroot + "/pluginfile.php/" + M.cfg.contextid + "/theme_cass/" + path;
            };

            require.config({
                paths: {
                    // Vendor code.
                    "theme_cass/TweenMax": [
                        "https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.2/TweenMax.min",
                        // CDN Fallback - whoop whoop!
                        pluginJSURL("vendorjs/TweenMax.min")
                    ]
                },
                shim: {
                    "theme_cass/TweenMax": {
                        exports: "TweenMax"
                    }
                }
            });
        })();

        var self = this;

        var scrollIn = function(selector) {
            $(selector).show().animate({right: "20px", opacity: 1}, 1000);
        };

        var scrollOut = function(selector) {
            $(selector).animate({right: "-600px", opacity: 0.5}, 200, function(){ $(selector).hide();});
        };

        var slideNextActivity = function() {
            scrollIn('#activitycompletemodal');
        };

        var popCompletion = function() {
            require(['theme_cass/TweenMax'], function(TweenMax) {
                $(".next_activity_overlay").fadeTo("slow", 0, function() {
                    $(this).hide();
                });
                Util.whenTrue(
                    () => {return $("#alertBox").length && $("#darkBackground").length;},
                    function() {
                        var $aBox = $("#alertBox");
                        var $darkBg = $("#darkBackground");
                        TweenMax.to($darkBg,  0,   {display: "block"});
                        TweenMax.to($darkBg,  0.3, {background: "rgba(0,0,0,0.4)", force3D: true});
                        TweenMax.to($aBox,    0,   {left: "calc(50% - 150px)", top: "calc(50% - 150px)", delay: "0.2"});
                        TweenMax.to($aBox,    0,   {display: "block", opacity: 1, delay: "0.2"});
                        TweenMax.to($aBox,    0,   {display: "block", scale: 0.2, opacity: 0, delay: "0.2"});
                        TweenMax.to($aBox,    0.3, {opacity: 1, force3D: true, delay: "0.2"});
                        TweenMax.to($aBox,    0.3, {scale:1, force3D: true, delay: "0.2"});
                        TweenMax.to($darkBg,  0.2, {backgroundColor: "rgba(0,0,0,0)", force3D: true, delay: "2"});
                        TweenMax.to($darkBg,  0.2, {display: "none", force3D: true, delay: "2"});
                        TweenMax.to($aBox,    0.2, {opacity: 0, display: "none", force3D: true, delay: "2",
                            onComplete: slideNextActivity});
                    }
                );
            });
        };

        this.addPopCompletion = function(mod) {
            if (typeof M.themeCass.settings.nextactivitymodaldialogdelay != 'undefined') {
                var manualPopActivities = ['page', 'book', 'wiki', 'feedback'];
                if (manualPopActivities.indexOf(mod.modname) === -1) {

                    setTimeout(
                        function() {
                            popCompletion();
                        },

                        M.themeCass.settings.nextactivitymodaldialogdelay
                    );
                }
            }
        };

        this.init = function(themeCass, mod) {
            M.themeCass = themeCass;
            if (M.themeCass.settings && M.themeCass.settings.fixheadertotopofpage) {
                // Hack to stop headroom kicking in on nav.
                $('body').append('<span style="display:none" class="notloggedin"></span>');
            }
            // Initialise breadcrumb js
            breadcrumb.init();

            var bindHsuforumCompletion = function() {
                $(".hsuforum-discussion, .hsuforum-reply").submit(function() {
                    setTimeout(
                        function() {
                            loadCompletion();
                        }, 3000
                    );

                });
            };

            var loadCompletion = function() {
                var type = 'completion';
                try {
                    $.ajax({
                        type: "GET",
                        async:  true,
                        url: M.cfg.wwwroot + '/theme/cass/rest.php?action=get_' + type + '&contextid=' +
                        mod.contextid, // M.cfg.context,
                        success: function(data) {
                            $('.completion-region').attr('data-content-loaded', '1');
                            $('.completion-region').html(data.html);
                            self.addPopCompletion(mod);
                        }
                    });
                } catch(err) {
                    window.console.error(err);
                }

                //bind to the control again
                bindHsuforumCompletion();
            };

            var setManualCompletion = function(cmid) {
                ajax.call([
                    {
                        methodname: 'theme_snap_course_module_completion',
                        args: {id: cmid, completionstate: 1},
                        fail: function(response) {
                            window.console.error(response);
                        },
                        done: function() {
                            const data = $('.next_activity_area').data('completion');
                            if (data) {
                                Templates.render('theme_cass/completionmodal', data)
                                    .then(function(output) {
                                        // Add template output to page.
                                        $('.completion-region').append(output);
                                    });
                            }
                            popCompletion();
                        }
                    }
                ], true, true);
            };

            var listeners = function() {
                // Alternate up/down chevron direction based on collapsable bootstrap elements
                $('[data-toggle="collapse"]').on('click', function () {
                    $(this).find("span.glyphicon").toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
                });

                //listener for completion button click
                //#completeclick
                $('.completeclick').on('click', function (e) {
                    e.preventDefault();
                    setManualCompletion($(this).data('cmid'));
                });

                //listener for completion modal close
                //#completeclick
                $('.completeclose').on('click', function (e) {
                    scrollOut('#activitycompletemodal');
                    e.preventDefault();
                });

                //Attach specific events for specific mod pages
                if (mod !== 'undefined' && mod !== null) {
                    if (mod.modname === 'hsuforum') {

                        //posting a reply inline via hsuforum:
                        // - Uses a YUI ajax call
                        // - replaces the article node tagged with the hsuforum-post-target class
                        // - this node is a child of the mod-hsuforum-posts-container div
                        // - this node can not be easily targetted by an event listener for a change event
                        // - can be targeted

                        // Unforunately can not easily attach an event to the hsuform-post-target div
                        // bind this function to the reply form submit that adds a timer
                        // to lazy check completion via ajax

                        bindHsuforumCompletion();
                    }
                }
            };

            listeners();
        };
    };
});
<?php

namespace theme_cass\output;

use html_writer;
use moodle_url;

/**
 * CASS renderer - renderer methods applicable only to this theme - i.e. that are not overrides to core_renderer, etc.
 * @package theme_cass\output
 */
class renderer extends \renderer_base {

    const EXCLUDE_POPUP_MODS = ['page', 'book', 'wiki'];

    /**
     * Get mod completion
     *
     * If we're on a 'mod' page, retrieve the mod object and check it's completion state in order to conditionally
     * pop a completion modal and show a link to the next activity in the footer.
     * @return list of $mod object, show completed activity (bool), and show completion modal (bool)
     */
    private static function get_completion_footer($nextactivityinfooter,
                                                  $nextactivitymodaldialog, $nextactivitymodaldialogtolerance) {

        global $PAGE, $COURSE, $USER, $DB;

        $mod = null;
        $nextmod = null;
        $showcompletionnextactivity = false;
        $showcompletionmodal = false;

        // Short-circuit if the user is editing.
        if ($PAGE->user_is_editing()) {
            return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
        }

        // Short-circuit if neither of nextactivityinfooter or nextactivitymodaldialog are set.
        if (empty($nextactivityinfooter) && empty($nextactivitymodaldialog)) {
            return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
        }

        // Short-circuit if we are not on a mod page, and allow restful access
        $pagepath = explode('-', $PAGE->pagetype);
        if ($PAGE->pagetype == 'admin' || $pagepath[0] != 'mod' && $PAGE->pagetype != 'theme-cass-rest') {
            return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
        }

        // Only continue evaluating if course completion is enabled.
        if ($COURSE->enablecompletion != COMPLETION_ENABLED) {
            return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
        }

        // Make sure we have a mod object.
        $mod = $PAGE->cm;
        if (!is_object($mod)) {
            //$PAGE->cm won't be loaded on a restful load
            $modinfo = get_fast_modinfo($COURSE->id);

            $context = $PAGE->context;

            if ($context->contextlevel == CONTEXT_MODULE) {

                $instanceid = $context->instanceid;
                if (!empty($instanceid)) {

                    $mod = $modinfo->get_cm($instanceid);

                    if (!is_object($mod)) {
                        return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
                    }
                }
            }
        }

        // Completion tracking states are defined in /lib/completionlib.php
        /*
            COMPLETION_TRACKING_NONE        0
            COMPLETION_TRACKING_MANUAL      1
            COMPLETION_TRACKING_AUTOMATIC   2
        */
        // Check completion setting of current mod, and set showcompletionnextactivity.
        // If the completion tracking is set to manual, we don't want to pop completion,
        // but we do want to potentially show the next activity in the footer.
        if ($mod) {
            if (isset($mod->completion) && intval($mod->completion) === COMPLETION_TRACKING_MANUAL) {
                if ($nextactivityinfooter && strpos($PAGE->url, '/course/modedit.php') === false) {
                    $showcompletionnextactivity = true;
                }
            } else {
                // Defensive: short-circuit is completion tracking isn't enabled.
                if ($mod->completion != COMPLETION_TRACKING_AUTOMATIC) {
                    return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
                }

                // Read relevant associated completion record.
                $completion = $DB->get_record('course_modules_completion', array('coursemoduleid' => $mod->id, 'userid' => $USER->id));

                // Ensure completion record was read from the database.
                if (empty($completion)) return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);

                // Completion states are defined in /lib/completionlib.php
                /*
                    COMPLETION_INCOMPLETE       0
                    COMPLETION_COMPLETE         1
                    COMPLETION_COMPLETE_PASS    2
                    COMPLETION_COMPLETE_FAIL    3
                    COMPLETION_UNKNOWN         -1
                    COMPLETION_GRADECHANGE     -2
                */

                // Ensure completion state exists on the record. This will ignore incompleted activities, which is incidentally appropriate.
                if (empty($completion->completionstate)) {
                    return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
                }

                // For our purposes, both 'complete' and 'complete_pass' are considered completed.
                // Any other status is 'incomplete'.
                if ($completion->completionstate != COMPLETION_COMPLETE && $completion->completionstate != COMPLETION_COMPLETE_PASS) {
                    return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
                }

                // Check theme setting on whether to display the next activity in the footer
                if ($nextactivityinfooter && strpos($PAGE->url, '/course/modedit.php') === false) {
                    $showcompletionnextactivity = true;
                }

                // Check whether to pop a completion modal dialog based on a theme setting
                if (!empty($nextactivitymodaldialog)) {


                    // Ensure timemodified value exists on the record / object
                    if (!empty($completion->timemodified)) {


                        // We are only interested in popping a completed modal dialog if it has happened in the last thirty seconds
                        // 'thirty seconds' is a variable theme setting. This is to prevent future loading of the completed activity from
                        // re-popping the completed dialog. This could have been saved against the user in a more deliberate way.
                        // Defensive programming: Use absolute value in forumla to offset potential concurrency issues from multiple webservers.
                        // Use tolerance in seconds from theme setting.
                        if (abs(time() - $completion->timemodified) < $nextactivitymodaldialogtolerance) {
                            $showcompletionmodal = true;
                        }
                    }
                }
            }
        }

        // If either setting is true, then we need to work out what the next activity is.
        if ($showcompletionnextactivity || $showcompletionmodal) {

            $currentcmidfoundflag = false;
            $nextmod = false;

            // Get all course modules from modinfo
            $cms = $mod->get_modinfo()->cms;

            // Loop through all course modules to find the next mod
            foreach ($cms as $cmid => $cm) {

                // The nextmod must be after the current mod
                // Keep looping until the current mod is found (+1)
                if (!$currentcmidfoundflag) {
                    if ($cmid == $mod->id) {
                        $currentcmidfoundflag = true;
                    }

                    // short circuit to next mod in list
                    continue;

                } else {
                    // (The continue and else condition are not mutually neccessary
                    // but the statement block is more clear with the explicit else)

                    // The current activity has been found... set the next activity to the first
                    // user visible mod after this point.
                    if ($cm->uservisible) {
                        $nextmod = $cm;
                        break;
                    }
                }
            }
        }

        return array($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal);
    }

    private function module_is_complete(\cm_info $mod) {
        global $DB, $USER;
        $completion = $DB->get_record('course_modules_completion', [
                'coursemoduleid' => $mod->id,
                'userid' => $USER->id
            ]
        );
        return $completion && intval($completion->completionstate) === COMPLETION_COMPLETE;
    }

    /**
     * Render mod completion
     *
     * If we're on a 'mod' page, retrieve the mod object and check it's completion state in order to conditionally
     * pop a completion modal and show a link to the next activity in the footer.
     * @return list of $mod object, show completed activity (bool), and show completion modal (bool)
     */
    public function render_completion_footer($nextactivityinfooter,
                                             $nextactivitymodaldialog, $nextactivitymodaldialogtolerance) {

        global $PAGE, $COURSE;

        // Initialise return variable.
        $output = '';

        // Retrieve mod object, next mod object, and whether the completion footer and completion modal should be displayed.
        list ($mod, $nextmod, $showcompletionnextactivity, $showcompletionmodal) = self::get_completion_footer(
            $nextactivityinfooter,
            $nextactivitymodaldialog,
            $nextactivitymodaldialogtolerance
        );

        // if not appropriate to render anything, return empty string;
        if (!$showcompletionnextactivity && !$showcompletionmodal) return $output;

        // Main completion message
        $completiontext = '';

        // The call-to-action forward arrow link
        $forwardlinklabel = '';
        $forwardlinkname = '';
        $forwardlinkurl =  '';

        if ($nextmod) {
            // 'You released the next activity '
            $completiontext = get_string('nextactivitydesc', 'theme_cass');

            // 'Next Activity'
            $forwardlinktext = get_string('nextactivity', 'theme_cass');
            $forwardlinkname = $nextmod->name;
            $forwardlinkurl =  $nextmod->url;

        } else {
            // If there is no "next mod" then assume we are at the final mod, and the call to action
            // is to return to the course page.

            // 'Course page '
            $completiontext = get_string('coursepagedesc', 'theme_cass');

            // 'To course page'
            $forwardlinktext = get_string('coursepage', 'theme_cass');
            $courseurl = new moodle_url('/course/view.php', ['id' => $COURSE->id], 'section-' . $mod->sectionnum);
            $forwardlinkurl =  $courseurl;
            $forwardlinkname = $COURSE->fullname;
        }

        if ($showcompletionmodal && !in_array($mod->modname, self::EXCLUDE_POPUP_MODS)) {
            $data = [
                'completiontext' => $completiontext,
                'forwardlinkurl' => $forwardlinkurl.'',
                'forwardlinktext' => $forwardlinktext,
                'forwardlinkname' => $forwardlinkname
            ];
            $output .= $this->render_from_template('theme_cass/completionmodal', $data);

            $PAGE->requires->js_call_amd('theme_cass/cass', 'addPopCompletion', [$mod]);
        }

        if ($showcompletionnextactivity) {
            $manualcomp = intval($mod->completion) === COMPLETION_TRACKING_MANUAL;
            $modexcludepopup = in_array($mod->modname, self::EXCLUDE_POPUP_MODS);
            $showoverlay = ($manualcomp || $modexcludepopup) && !$this->module_is_complete($mod);

            $data = (object) [
                'completiontext' => $completiontext,
                'forwardlinkurl' => $forwardlinkurl.'',
                'forwardlinktext' => $forwardlinktext,
                'forwardlinkname' => $forwardlinkname,
                'modid' => $mod->id,
                'showoverlay' => $showoverlay
            ];
            $json = json_encode($data);
            $data->compjson = $json;
            $output .= $this->render_from_template('theme_cass/nextactivityarea', $data);
        }

        //'completion-region'
        return $output;

    }
}
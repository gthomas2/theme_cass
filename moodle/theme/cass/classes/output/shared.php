<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renderer functions shared between multiple renderers.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_cass\output;

defined('MOODLE_INTERNAL') || die();

class shared extends \theme_snap\output\shared {

    /**
     * Javascript required by both standard header layout and flexpage layout
     *
     * @return void
     */
    public static function page_requires_js() {
        global $PAGE;

        // We want to have some theme settings available to javascript.
        // But some theme settings may be sensitive so only pass which settings are required.
        $requiredsettings = array('fixheadertotopofpage', 'nextactivitymodaldialogdelay');
        $themesettings = array_intersect_key((array)$PAGE->theme->settings, array_flip($requiredsettings));

        // We want some information about the activity to be available to javascript on mod pages
        // There exist various pure javascript techniques for attempting to determine this infomation
        // from the renderered HTML, but nothing beats being explicit.
        $mod = null;
        if (explode('-', $PAGE->pagetype)[0] == 'mod') {
            if (is_object($PAGE->cm)) {
                $mod = array(
                    'modname' => $PAGE->cm->modname,
                    'contextid' => $PAGE->cm->context->id
                );
            }
        }

        $cassobj = (object) [
           'settings' => $themesettings
        ];
        $PAGE->requires->js_call_amd('theme_cass/cass', 'init', [$cassobj, $mod]);
        parent::page_requires_js();
    }
}
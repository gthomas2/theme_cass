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

namespace theme_cass;

defined('MOODLE_INTERNAL') || die;

use core\event\user_loggedout;

/**
 * Cass event handlers.
 *
 * @package   theme_cass
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_handlers {
    // Redirect to specific URL instead of default login page.
    public static function user_loggedout (user_loggedout $event) {
        global $redirect;

        // This event gets called for every user logout, regardless of whether snap is the active theme, or whether
        // the site allows user themes and regardless of the user theme setting.
        $user  = $event->get_record_snapshot('user', $event->objectid);

        if (get_config('core', 'theme') == 'cass' || get_config('core', 'allowuserthemes') && $user->theme == 'cass') {
            $theme = \theme_config::load('cass');
            if (!empty($theme->settings->logoutredirection)) {
                $redirect = $theme->settings->logoutredirection;
            }
        }

    }
}
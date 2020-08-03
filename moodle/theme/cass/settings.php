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
 * Snap settings.
 *
 * @package   theme_cass
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__.'/lib.php');

$checked = '1';
$unchecked = '0';

$settings = new theme_boost_admin_settingspage_tabs('themesettingcass', 'CASS');

$cascoursesettings = 'require("'.$CFG->dirroot.'/theme/cass/settings/course_settings.php");';

theme_cass_require_snap_settings('snap_basics.php', $settings);
theme_cass_require_snap_settings('cover_settings.php', $settings);
theme_cass_require_snap_settings('personal_menu_settings.php', $settings);
theme_cass_require_snap_settings('feature_spots_settings.php', $settings);
theme_cass_require_snap_settings('featured_courses_settings.php', $settings);
theme_cass_require_snap_settings('course_settings.php', $settings, $cascoursesettings);
theme_cass_require_snap_settings('social_media_settings.php', $settings);
theme_cass_require_snap_settings('navigation_bar_settings.php', $settings);
theme_cass_require_snap_settings('categories_color_settings.php', $settings);
theme_cass_require_snap_settings('profile_based_branding.php', $settings);

require(__DIR__.'/settings/general_settings.php');

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

defined('MOODLE_INTERNAL') || die;// Main settings.

$casssettings = new admin_settingpage('themecassgeneral', get_string('generalsettings', 'theme_cass'));

// Custom copyright notice.
$name = 'theme_cass/copyrightnotice';
$title = new lang_string('copyrightnotice', 'theme_cass');
$description = new lang_string('copyrightnoticedesc', 'theme_cass');
$default = '&nbsp;';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$casssettings->add($setting);

// Font include.
$fontloader = 'theme_cass/fontloader';
$title = new lang_string('fontloader', 'theme_cass');
$description = new lang_string('fontloaderdesc', 'theme_cass');
$default = new lang_string('fontloaderdefault', 'theme_cass');
$setting = new admin_setting_configtextarea($fontloader, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$casssettings->add($setting);

// CSS Post Process on/off.
$name = 'theme_cass/csspostprocesstoggle';
$title = new lang_string('csspostprocesstoggle', 'theme_cass');
$description = new lang_string('csspostprocesstoggledesc', 'theme_cass');
$checked = '1';
$unchecked = '0';
$default = $checked;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default, $checked, $unchecked);
$setting->set_updatedcallback('theme_reset_all_caches');
$casssettings->add($setting);

// Logout redirection
$name = 'theme_cass/logoutredirection';
$title = new lang_string('logoutredirection', 'theme_cass');
$description = new lang_string('logoutredirectiondesc', 'theme_cass');
$default = 'https://webapps.city.ac.uk/globalfinancemsc/';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$casssettings->add($setting);

$settings->add($casssettings);
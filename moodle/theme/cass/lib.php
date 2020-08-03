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
 * Standard library functions for cass theme.
 *
 * @package   theme_cass
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/coursepageredirect.php';

function theme_cass_get_main_scss_content($theme) {
    global $CFG;

    // Note, the following code is not fully used yet, only the hardcoded
    // pre and post scss files will be loaded, not any presets defined by
    // settings.

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');

    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_snap', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_snap and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        $scss = '@import "boost";';
    }

    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    $pre = file_get_contents($CFG->dirroot . '/theme/snap/scss/pre.scss');
    // Override the Snap pre with Cass customisations.
    $pre .= file_get_contents($CFG->dirroot . '/theme/cass/scss/pre.scss');
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->dirroot . '/theme/cass/scss/post.scss');

    // Combine them together.
    $combined = $pre . "\n" . $scss . "\n" . $post;
    file_put_contents('/Users/guy/tmp/web/cass.combined.scss', $combined);
    return $combined;
}


/**
 * Enables snap settings files to be used within the CASS settings file.
 * It does this by rewriting some settings from being snap specific to cass specific.
 * @param $filename
 * @param theme_boost_admin_settingspage_tabs $settings
 * @param string $insertsettings
 */
function theme_cass_require_snap_settings($filename, theme_boost_admin_settingspage_tabs $settings,
                                          $insertsettings = null) {
    $str = file_get_contents(__DIR__.'/../../theme/snap/settings/'.$filename);
    $search = <<<'SRCH'
/\$name(?:|\s*)=(?:|\s*)'theme_snap\//
SRCH;
    $replace = '$name = \'theme_cass/';
    $str = preg_replace($search, $replace, $str);

    $search = <<<'SRCH'
/admin_settingpage(?:|s*)\(\'themesnap/
SRCH;
    $replace = 'admin_settingpage(\'themecass';
    $str = preg_replace($search, $replace, $str);
    if (!empty($insertsettings)) {
        $regex = '/\$settings->add\(\$snapsettings\);/';

        $replacement = "\n\n".$insertsettings."\n\n".'$settings->add($snapsettings);';

        $str = preg_replace($regex, $replacement, $str);
    }

    /**
     * Like requiring from a file but instead uses a string.
     * @param $code
     * @return mixed
     */
    $rfs = function ($code) use($settings) {
        global $CFG, $DB; // These globals are used by dynamic requires.

        // Note - although $checked and $unchecked appear to be unused, they are actually used by required files.
        // So please don't remove them!
        $checked = '1';
        $unchecked = '0';
        $tmp = tmpfile ();
        $tmpf = stream_get_meta_data ( $tmp );
        $tmpf = $tmpf ['uri'];
        fwrite ( $tmp, $code );
        $ret = require $tmpf;
        fclose ( $tmp );
        return $ret;
    };

    $rfs($str);
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_cass_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    $pluginpath = __DIR__.'/';

    if ($filearea === 'vendorjs') {
        // Typically CDN fall backs would go in vendorjs.
        $path = $pluginpath.'vendorjs/'.implode('/', $args);
        send_file($path, basename($path));
        return true;
    }

    $coverimagecontexts = [CONTEXT_SYSTEM, CONTEXT_COURSE, CONTEXT_COURSECAT];

    // System level file areas.
    $sysfileareas = [
        'logo',
        'favicon',
        'fs_one_image',
        'fs_two_image',
        'fs_three_image',
        'slide_one_image',
        'slide_two_image',
        'slide_three_image'
    ];

    if ($context->contextlevel == CONTEXT_SYSTEM && in_array($filearea, $sysfileareas)) {
        $theme = theme_config::load('snap');
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else if (in_array($context->contextlevel, $coverimagecontexts)
        && $filearea == 'coverimage' || $filearea == 'coursecard') {
        theme_snap_send_file($context, $filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}
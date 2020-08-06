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
 * Cass core renderer.
 * Overrides snap core renderer.
 *
 * @package   theme_cass
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_cass\output;

defined('MOODLE_INTERNAL') || die();

use html_writer;
use moodle_url;
use context_system;
use breadcrumb_navigation_node;

class core_renderer extends \theme_snap\output\core_renderer {

    /**
     * Get the discussion type of a specific discussion's forum.
     * @param int $discussionid
     * @return mixed
     * @throws \dml_exception
     */
    private function get_discussion_hsuforum_type(int $discussionid): string {
        global $DB;

        $sql = "
            SELECT type
              FROM {hsuforum} hf
              JOIN {hsuforum_discussions} hfd ON hfd.forum = hf.id
             WHERE hfd.id = ?
        ";

        $forumtype = $DB->get_field_sql($sql, [$discussionid]);
        return $forumtype;
    }

    /**
     * Add sub-theme-cass to body class.
     * @param array $additionalclasses
     * @return array|string
     */
    public function body_css_classes(array $additionalclasses = array()) {
        $additionalclasses[] = 'sub-theme-cass';

        if (strpos(qualified_me(), 'mod/hsuforum/discuss.php?d') !== false) {
            $discussionid = optional_param('d', null, PARAM_INT);
            if ($discussionid !== null) {
                $type = $this->get_discussion_hsuforum_type($discussionid);
                $additionalclasses[] = 'hsforum-type-'.$type;
            }
        }

        return parent::body_css_classes($additionalclasses);
    }

    /**
     * This renders the navbar.
     * Uses bootstrap compatible html.
     * @param string $coverimage
     */
    public function navbar($coverimage = '') {
        global $COURSE, $CFG, $PAGE;

        require_once($CFG->dirroot.'/course/lib.php');

        $breadcrumbs = '';
        $courseitem = null;
        $attr['class'] = 'js-snap-pm-trigger';

        if (!empty($coverimage)) {
            $attr['class'] .= ' mast-breadcrumb';
        }
        $snapmycourses = html_writer::link('#', get_string('menu', 'theme_snap'), $attr);

        $modpage = $this->page->cm && !empty($this->page->cm->id);

        /** @var breadcrumb_navigation_node $item */
        foreach ($this->page->navbar->get_items() as $item) {
            $item->hideicon = true;

            $ismodlink = $item->action && (strpos($item->action->get_path(), '/mod') === 0);

            // Remove link to current page - n.b. needs improving.
            if ($item->action == $this->page->url) {
                if (!$modpage || !$ismodlink) {
                    continue;
                }
            }

            // Remove link to home/dashboard as site name/logo provides the same link.
            if ($item->key === 'home' || $item->key === 'myhome' || $item->key === 'dashboard') {
                continue;
            }

            // Never show the my courses link.
            if ($item->key === 'mycourses') {
                continue;
            }

            if ($modpage && $ismodlink) {
                // By default the module breadcrumb should be hidden.
                $style = ' style="opacity: 0; display: none;"';
                $breadcrumbs .= '<li class="breadcrumb-item"'.$style.'>';
                $breadcrumbs .= html_writer::link($item->action, $item->text);
                $breadcrumbs .= '</li>';
                continue;
            }

            // For Admin users - When default home is set to dashboard, let admin access the site home page.
            if (!$modpage && $item->key === 'myhome' && has_capability('moodle/site:config', context_system::instance())) {
                $breadcrumbs .= '<li class="breadcrumb-item">';
                $breadcrumbs .= html_writer::link(new moodle_url('/', ['redirect' => 0]), get_string('sitehome'));
                $breadcrumbs .= '</li>';
                continue;
            }

            // Replace my courses none-link with link to snap personal menu.
            if (!$modpage && $item->key === 'mycourses') {
                $breadcrumbs .= '<li class="breadcrumb-item">' .$snapmycourses. '</li>';
                continue;
            }

            if ($item->type == \navigation_node::TYPE_COURSE) {
                $courseitem = $item; // We need to set this before we potentially leave.
                if ($modpage) {
                    // We don't want to include the section when we are on a mod view page.
                    continue;
                }
            }

            if ($item->type == \navigation_node::TYPE_SECTION) {
                if ($courseitem != null) {
                    $url = $courseitem->action->out(false);
                    $item->action = $courseitem->action;
                    $sectionnumber = $this->get_section_for_id($item->key);

                    // Append section focus hash only for topics and weeks formats because we can
                    // trust the behaviour of these formats.
                    if ($COURSE->format == 'topics' || $COURSE->format == 'weeks') {
                        $url .= '#section-'.$sectionnumber;
                        if ($item->text == get_string('general')) {
                            $item->text = get_string('introduction', 'theme_snap');
                        }
                    } else {
                        $url = course_get_url($COURSE, $sectionnumber);
                    }
                    $item->action = new moodle_url($url);
                }
            }

            // Only output breadcrumb items which have links.
            if ($item->action !== null) {
                $attr = [];
                if (!empty($coverimage)) {
                    $attr = ['class' => 'mast-breadcrumb'];
                }
                $link = html_writer::link($item->action, $item->text, $attr);
                $breadcrumbs .= '<li class="breadcrumb-item">' .$link. '</li>';
            }
        }

        if (!empty($breadcrumbs)) {
            return '<ol class="breadcrumb">' .$breadcrumbs .'</ol>';
        }
    }
}

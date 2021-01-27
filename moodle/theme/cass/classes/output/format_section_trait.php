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
 * Trait - format section
 * Code that is shared between course_format_topic_renderer.php and course_format_weeks_renderer.php
 * Used for section outputs.
 *
 * @package   theme_cass
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_cass\output;

defined('MOODLE_INTERNAL') || die();

use theme_snap\renderables\course_action_section_highlight;
use theme_snap\renderables\course_action_section_move;
use theme_snap\renderables\course_action_section_visibility;
use theme_snap\renderables\course_action_section_delete; // This one isn't overriden by cass.
use theme_snap\output\format_section_trait as snap_section_trait;

/**
 * We are basically using the Snap format section trait but overriding it to use the theme_cass renderables.
 * Trait format_section_trait
 * @package theme_cass\output
 */
trait format_section_trait {

    use snap_section_trait;

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {

        if ($section->section === 0) {
            return [];
        }

        if ($onsectionpage) {
            $baseurl = course_get_url($course, $section->section);
        } else {
            $baseurl = course_get_url($course);
        }
        $baseurl->param('sesskey', sesskey());

        $controls = array();

        $moveaction = new course_action_section_move($course, $section, $onsectionpage);
        $controls[] = $this->render($moveaction);

        $visibilityaction = new course_action_section_visibility($course, $section, $onsectionpage);
        $controls[] = $this->render($visibilityaction);

        $deleteaction = new course_action_section_delete($course, $section, $onsectionpage);
        $controls[] = $this->render($deleteaction);

        $highlightaction = new course_action_section_highlight($course, $section, $onsectionpage);
        $controls[] = $this->render($highlightaction);

        return $controls;
    }

    /**
     * @param course_action_section_move $action
     * @return mixed
     * @throws \moodle_exception
     */
    public function render_course_action_section_move(course_action_section_move $action) {
        $data = $action->export_for_template($this);
        return $this->render_from_template('theme_snap/course_action_section', $data);
    }

    /**
     * @param course_action_section_visibility $action
     * @return mixed
     * @throws \moodle_exception
     */
    public function render_course_action_section_visibility(course_action_section_visibility $action) {
        $data = $action->export_for_template($this);
        return $this->render_from_template('theme_snap/course_action_section', $data);
    }

    /**
     * @param course_action_section_highlight $action
     * @return mixed
     * @throws \moodle_exception
     */
    public function render_course_action_section_highlight(course_action_section_highlight $action) {
        $data = $action->export_for_template($this);
        return $this->render_from_template('theme_snap/course_action_section', $data);
    }


}

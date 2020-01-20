<?php

namespace local_tlcore;

defined('MOODLE_INTERNAL') || die;

// core-specific rest actions
class actions {
    public static function get_component_strings(): array {
        $component = required_param('component', PARAM_ALPHANUMEXT);
        $strman = get_string_manager();
        return $strman->load_component_strings($component, current_language());
    }
}
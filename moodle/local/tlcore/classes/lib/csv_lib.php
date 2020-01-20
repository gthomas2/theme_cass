<?php

namespace local_tlcore\lib;

defined('MOODLE_INTERNAL') || die;

class csv_lib {
    public static function load_from_filepath(string $filepath, string $ident) {
        global $CFG;

        require_once($CFG->libdir.'/csvlib.class.php');

        $iid = \csv_import_reader::get_new_iid($ident);
        $cir = new \csv_import_reader($iid, $ident);
        $cir->load_csv_content(file_get_contents($filepath), 'utf-8', 'comma');
        $cir->init();
        return $cir;
    }
}
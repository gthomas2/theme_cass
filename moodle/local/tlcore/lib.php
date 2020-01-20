<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Note - this used to be a hook (local_tlcore_before_standard_html_head) which is really bad because it forces its
 * requires on every page. It should only ever be requiring css and js on the pages relevant to itself for two reasons:
 * 1) It's more efficient that way 2) It doesn't cause conflicts with other plugins.
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_tlcore_standard_page_requires() {
  global $PAGE; /** @var moodle_page $PAGE */

  // include assets from tlcore
  $PAGE->requires->css(new moodle_url('/local/tlcore/vendor.css'));
  $PAGE->requires->css(new moodle_url('/local/tlcore/bundle.css'));
  $PAGE->requires->js(new moodle_url('/local/tlcore/vendor.js'));
  $PAGE->requires->js(new moodle_url('/local/tlcore/bundle.js'));
}

function local_tlcore_before_standard_html_head() {
    global $PAGE;
    // This is truly awful but we need this code to be available before AMD 'define' code.
    // It resolves a chicken and egg scenario.
    $PAGE->requires->js(new moodle_url('/local/tlcore/js/amdtools.js'));
}

if (!function_exists('dd')) {

  /**
   * Var dump and die.
   * @return void
   */
  function dd(...$things) {
    var_dump(...$things);
    exit;
  }
}

if (!function_exists('dd_sql')) {

    /**
     * Var dump sql with params interpolated.
     * @param string $sql
     * @param array|null $params
     * @param int $mode
     */
    function dd_sql($sql, $params = null, $mode = SQL_PARAMS_QM) {
        global $CFG;
        $sql = str_replace('{', $CFG->prefix, $sql);
        $sql = str_replace('}', '', $sql);

        if ($mode === SQL_PARAMS_QM) {
            foreach ($params as $param) {
                $sql = substr_replace($sql, "'$param'", strpos($sql, '?'), 1);
            }
        } else {
            $pkeys = array_keys($params);

            // Note the reversal is important - it means if you have :cat and :cat2, :cat2 will get replaced first.
            $pkeys = array_reverse($pkeys, true);
            foreach ($pkeys as $key) {
                $val = $params[$key];
                $sql = str_replace(':' . $key, "'$val'", $sql);
            }
        }

        mtrace($sql);
        exit;
    }
}
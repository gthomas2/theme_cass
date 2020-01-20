<?php
namespace local_tlcore\datatable;

defined('MOODLE_INTERNAL') || die;

use stdClass;

abstract class base_datatable_sql extends base_datatable {

    /**
     * @var array
     */
    protected $rawdata = [];

    /**
     * Return the sql and parameters for this datatable.
     * @param query $query
     * @return array
     */
    abstract protected function sql_and_params(query $query): array;

    /**
     * Transform rows so that they can show dates correctly, etc.
     * @param stdClass $row
     * @return stdClass | null
     */
    protected function transform_row(stdClass $row): ?stdClass {
        return $row;
    }

    protected function get_data_count(query $query): int {
        /**@var \moodle_database $DB*/
        global $DB;

        list ($sqlstring, $params) = $this->sql_and_params($query);
        if (empty($sqlstring)) {
            return 0;
        }
        $sqlstring = "SELECT count(1) AS total FROM ($sqlstring) subqry";
        return $DB->count_records_sql($sqlstring, $params);
    }

    protected function get_data(query $query): array {
        global $DB;

        static $data = null;

        if ($data !== null) {
            return $data;
        }

        if (!empty($query->download)) {
            $query->offset = 0;
            $query->limit = 0;
        }

        list ($sqlstring, $params) = $this->sql_and_params($query);
        $rs = $DB->get_recordset_sql($sqlstring, $params, $query->offset, $query->limit);

        // Note we have to set rawdata because a recordset ($rs) can only be looped through once and
        // transform_row might need access to the entire dataset to do the transform.
        foreach ($rs as $row) {
            $this->rawdata[] = $row;
        }

        foreach ($this->rawdata as $row) {
            $row = $this->transform_row($row);
            $data[] = $row;
        }
        return is_array($data) ? $data : [];
    }

    /**
     * Convert an array of select fields to a map whereby aliases can be resolved to verbose db field - i.e table.field
     * @param array $selectfields
     * @return array
     */
    protected function convert_selectfields_to_filter_map(array $selectfields) {
        $map = [];
        foreach ($selectfields as $selectfield) {
            if (stripos($selectfield, ' as ') !== false) {
                $arr = preg_split("/ as /i", $selectfield);
                $map[$arr[1]] = $arr[0];
            } else if (stripos($selectfield, '.') !== false) {
                $arr = explode('.', $selectfield);
                $map[$arr[1]] = $selectfield;
            }
        }
        return $map;
    }

    /**
     * @param array $selectfields
     * @param query $query
     * @return array
     */
    protected function get_filter_sql_param(array $selectfields, query $query) {
        global $DB;

        static $f = 0;
        $f ++;

        $filtersql = null;
        $filterparam = null;
        if (empty($query->filter)) {
            return [$filtersql, $filterparam];
        }
        $filtermap = $this->convert_selectfields_to_filter_map($selectfields);
        $filterarr = explode('~', $query->filter);
        $field = $filterarr[0];
        if (isset($filtermap[$field])) {
            $field = $filtermap[$field]; // Convert alias back to verbose table name so we can use it in WHERE clause.
        }
        $term = $filterarr[1];
        $pname = 'filt'.$f;
        $filtersql = $DB->sql_like($field, ':' . $pname, false);
        $filterparam = '%'.$DB->sql_like_escape($term).'%';
        return [$filtersql, $pname, $filterparam];
    }
}
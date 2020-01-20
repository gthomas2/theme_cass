<?php
namespace local_tlcore\datatable;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/csvlib.class.php');

abstract class base_datatable {

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var integer
     */
    protected $count = 0;

    /**
     * @var bool
     */
    protected $requirelogin = true;
    /**
     * @var array
     */
    protected $data = [];

    public function __construct() {
        $this->set_columns();
    }

    abstract protected function set_columns();

    abstract protected function get_data_count(query $query): int;

    abstract protected function get_data(query $query): array;

    /**
     * Rreturn list of columns that should be classified as exportable (any col fields prefixed with __ are ignored).
     * @return array
     */
    protected function exportable_columns(): array {
        return array_filter($this->columns, function($item) {
            return strpos($item->field, '__') !== 0;
        });
    }

    /**
     * Download csv
     * @param string|null $filename
     */
    protected function download_csv(?string $filename = null) {
        $cvw = new \csv_export_writer();
        if ($filename) {
            $cvw->filename = clean_filename($filename);
        }
        $headerrow = [];
        $cols = $this->exportable_columns();
        foreach ($cols as $column) {
            $headerrow[] = $column->title;
        }
        $cvw->add_data($headerrow);
        foreach ($this->data as $item) {
            $row = [];
            foreach ($cols as $column) {
                $itema = (array) $item;
                if (isset($itema[$column->field])) {
                    $row[] = $itema[$column->field];
                } else {
                    $row[] = ''; // Intentional null string.
                }
            }
            $cvw->add_data($row);
        }
        $cvw->download_file();
    }

    /**
     * Download xlsx.
     * Based on mod/data/lib.php data_export_xls.
     * @param string|null $filename
     */
    protected function download_xls(?string $filename = null) {
        global $CFG;
        require_once("$CFG->libdir/excellib.class.php");
        if ($filename) {
            $filename = clean_filename($filename);
        } else {
            $filename = 'download.xlsx';
        }

        $workbook = new \MoodleExcelWorkbook($filename);
        $workbook->send($filename);
        $worksheet = [];
        $worksheet[0] = $workbook->add_worksheet('');

        $rowno = 0;
        $export = [];
        $headerrow = [];
        $cols = $this->exportable_columns();
        foreach ($cols as $column) {
            $headerrow[] = $column->title;
        }
        $export[] = $headerrow;
        foreach ($this->data as $item) {
            $row = [];
            foreach ($cols as $column) {
                $itema = (array) $item;
                if (isset($itema[$column->field])) {
                    $row[] = $itema[$column->field];
                } else {
                    $row[] = ''; // Intentional null string.
                }
            }
            $export[] = $row;
        }

        foreach ($export as $row) {
            $colno = 0;
            foreach($row as $col) {
                $format = null;
                if ($rowno === 0) {
                    $format = [
                        'bg_color' => 'silver'
                    ];
                }
                $worksheet[0]->write($rowno, $colno, $col, $format);
                $colno++;
            }
            $rowno++;
        }
        $workbook->close();
        die;
    }


    /**
     * Run query.
     *
     * @param query $query
     * @return array
     */
    public function run_query(query $query) {

        if ($this->requirelogin) {
            require_login();
        }

        if(method_exists($this, 'require_capabilities')) {
            $this->require_capabilities();
        }

        $this->count = $this->get_data_count($query);
        $this->data  = $this->get_data($query);

        if (!empty($query->download)) {
            $filename = optional_param('filename', null, PARAM_TEXT);
            switch ($query->download) {
                case 'csv' : $this->download_csv($filename); break;
                case 'xls' : $this->download_xls($filename); break;
            }
        }

        return [
            'columns' => $this->columns,
            'data'    => $this->data,
            'total'   => $this->count,
            'query'   => $query
        ];
    }

}

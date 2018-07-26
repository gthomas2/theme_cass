<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'CHANGEME';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'root';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

$CFG->debug = 32767;
$CFG->debugdisplay = 1;

$CFG->wwwroot   = 'http://CHANGEME.test';
$CFG->dataroot  = '/path/to/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;


$CFG->phpunit_wwwroot   = 'http://CHANGEME.test';
$CFG->phpunit_dataroot  = '/path/to/moodledata/phpunit';
$CFG->phpunit_prefix    = 'phpu_';


require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

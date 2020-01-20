<?php

namespace local_tlcore\db;

defined('MOODLE_INTERNAL') || die;

use stdClass;

abstract class factory {
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var stdClass
     */
    protected $data;

    private function __construct() {
        $this->faker = self::get_faker();
    }

    public static function get_faker(): \Faker\Generator {
        require_once(__DIR__.'/../../vendor/autoload.php');
        return \Faker\Factory::create();
    }
    protected abstract function generate(): stdClass;

    public static function factory(?stdClass $defaults = null): factory {
        static $instance = null;
        if ($instance === null) {
            $class = get_called_class();
            $instance = new $class;
        }
        $instance->data = $instance->generate($defaults);
        return $instance;
    }

    public function get_data(): stdClass {
        return $this->data;
    }
}
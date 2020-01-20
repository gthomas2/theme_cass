<?php

namespace local_tlcore;

defined('MOODLE_INTERNAL') || die();

class testutil {
    /**
     * @var array
     */
    private static $downloads;

    /**
     * Is a php unit test running?
     * @return bool
     */
    public static function phpunit_test_running(): bool {
        return defined('PHPUNIT_TEST') && PHPUNIT_TEST === true;
    }

    /**
     * Register a download's content - for php unit tests.
     * @param $content
     * @param bool $reset
     */
    public static function register_download(string $content, $reset = false) {
        if (!self::phpunit_test_running()) {
            return;
        }
        if (empty(self::$downloads) || $reset) {
            self::$downloads = [];
        }
        self::$downloads[] = $content;
    }

    public static function get_downloads(): array {
        if (empty(self::$downloads)) {
            self::$downloads = [];
        }
        return self::$downloads;
    }

    public static function get_first_download(): string {
        if (empty(self::$downloads)) {
            self::$downloads = [];
        }
        return reset(self::$downloads);
    }

    public static function get_last_download(): string {
        if (empty(self::$downloads)) {
            self::$downloads = [];
        }
        return end(self::$downloads);
    }
}
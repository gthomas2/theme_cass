#!/usr/bin/php
<?php

/**
 * Class packagetests
 * Run tests for every plugin defined in package.json unless the plugin is present in
 * phpunit_blocklist.
 * Example package.json:
 *
 * {
 *   "name": "titus-moodle",
 *   "version": "1.0.0",
 *   "moodle": {
 *     "branch": 38,
 *     "tag": "latest-38",
 *     "plugins": [
 *       "auth/saml2",
 *       "availability/condition/role",
 *       "blocks/products_catalog",
 *     ],
 *     "phpunit_blocklist": [
 *       "auth/saml2"
 *     ]
 *   }
 * }
 */
class packagetests {

    protected function __construct() {
        // Note that package.json gets copied into the moodle root as project_package.json.
        // Ensure the file is present.
        $file = __DIR__.'/project_package.json';
        if (!file_exists($file)) {
            $this->trace("package.json (project_package.json) is missing. Cannot run tests.");
            die;
        }
        // Ensure the package.json file can be decoded.
        $contents = file_get_contents($file);
        $obj = json_decode($contents);
        if (empty($obj)) {
            $this->trace("Failed to decode $file. Cannot run tests.");
            die;
        }

        // Ensure that there are plugins available in package.json.
        $plugins = $obj->moodle->plugins;
        $phpunitblocklist = !empty($obj->moodle->phpunit_blocklist) ? $obj->moodle->phpunit_blocklist : [];
        if (empty($plugins)) {
            $this->trace("No plugins found in package.json. Cannot run tests.");
            die;
        }

        // Filter the plugins to be used for php unit tests according to blocklist.
        $phpunitplugins = array_diff($plugins, $phpunitblocklist);

        // Run PHP unit tests.
        $this->run_php_unit_tests($phpunitplugins);
    }

    /**
     * Run php unit tests for an array of plugins.
     * @param array $plugins
     */
    private function run_php_unit_tests(array $plugins) {
        $failedtests = [];
        foreach ($plugins as $plugin) {
            $testdirs = $this->get_phpunit_directories($plugin);
            foreach ($testdirs as $testdir) {
                $testdir = str_replace(__DIR__.'/', '', $testdir);
                $exec = "vendor/bin/phpunit  $testdir/. --test-suffix=test.php";
                $this->trace($exec);
                $retval = null;
                passthru($exec, $retval);
                if ($retval == 0) {
                    $this->trace("\n".'PHP UNIT TESTS PASSED FOR '.$testdir."\n");
                } else {
                    $this->trace("\n".'PHP UNIT TESTS FAILED FOR '.$testdir."\n");
                    $failedtests[] = $testdir;
                }
            }
        }
        if (!empty($failedtests)) {
            $this->trace("\nTHE FOLLOWING PHP UNIT TESTS FAILED:\n".implode("\n", $failedtests)."\n\n");
            exit(1); // Report an error back (0 = success, 1 - 255 is error).
        }
        $this->trace("\nALL PHP UNIT TESTS PASSED :-)\n");
    }

    /**
     * Use this to kick off this class.
     * @return bool|packagetests|void
     */
    public static function go() {
        static $ran = false;
        if ($ran) {
            // Can only be run once.
            return;
        }
        $ran = new packagetests();
        return $ran;
    }

    /**
     * Output to cli.
     * @param string $msg
     */
    private function trace(string $msg) {
        echo "$msg\n";
    }

    /**
     * Get all directories containing php unit tests.
     * @param string $plugin
     * @return array
     */
    private function get_phpunit_directories(string $plugin): array {
        $testdirs = [];
        $dir = new RecursiveDirectoryIterator(__DIR__.'/'.$plugin);
        $iterator = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($iterator, '/^.+\/tests\/.+_test\.php$/', RecursiveRegexIterator::GET_MATCH);
        foreach ($files as $file) {
            $dir = dirname(reset($file));
            if (!in_array($dir, $testdirs)) {
                $testdirs[] = $dir;
            }
        }
        return $testdirs;
    }
}

// Run the tests.
packagetests::go();
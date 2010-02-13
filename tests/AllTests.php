<?php

date_default_timezone_set('Europe/Berlin');
//this is some test stuff

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

/**
 * Test helper
 */
//require_once 'TestHelper.php';
require_once 'TestConfiguration.php';

require_once 'Edo/AllTests.php';

class AllTests
{
    public static function main()
    {
        $parameters = array();

        if (TESTS_GENERATE_REPORT && extension_loaded('xdebug')) {
            $parameters['reportDirectory'] = TESTS_GENERATE_REPORT_TARGET;
        }

        PHPUnit_TextUI_TestRunner::run(self::suite(), $parameters);
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Earth.org');

        $suite->addTest(Edo_AllTests::suite());
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}

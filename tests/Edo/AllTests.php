<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Edo_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'EventTest.php';
require_once 'Event/AllTests.php';

class Edo_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Earth.org - Edo_* testcases');
        $suite->addTestSuite('Edo_EventTest');
        $suite->addTest(Edo_Event_AllTests::suite());
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Edo_AllTests::main') {
    Edo_AllTests::main();
}

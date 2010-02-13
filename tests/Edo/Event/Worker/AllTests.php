<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Edo_Event_Worker_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'ManagerTest.php';


class Edo_Event_Worker_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Earth.org - Edo_Event_Worker');
        $suite->addTestSuite('Edo_Event_Worker_ManagerTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Edo_Event_Worker_AllTests::main') {
    Edo_Event_Worker_AllTests::main();
}

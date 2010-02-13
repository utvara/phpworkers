<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Edo_Event_Engine_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'FileTest.php';
require_once 'FactoryTest.php';

class Edo_Event_Engine_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Earth.org - Edo_Event_Engine');
        $suite->addTestSuite('Edo_Event_Engine_FileTest');
        $suite->addTestSuite('Edo_Event_Engine_FactoryTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Edo_Event_Engine_AllTests::main') {
    Edo_Event_Engine_AllTests::main();
}

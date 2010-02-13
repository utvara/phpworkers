<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . "/TestConfiguration.php");

require_once WORKSHOP_ROOT . "/Edo/Event/Engine/Factory.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine/File.php";
require_once WORKSHOP_ROOT . "/Edo/Event.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine/Factory.php";

/**
 * Batch_Video test case.
 */
class Edo_Event_Engine_FactoryTest extends PHPUnit_Framework_TestCase {

    protected $engine = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp ();
        $this->engine = Edo_Event_Engine_Factory::build($this->_getConfig());
    }

    protected function _getConfig()
    {
        $config = array();
        $config['base_path'] = TESTS_ROOT . '/data';
        $config['type'] = 'file';
        return $config;
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        FileEngineHelper::revertSystem($this->engine);
        $this->engine = null;
        parent::tearDown ();
    }

    protected function _getNewInstance($config = null)
    {
        $config['type'] = 'file';
        return Edo_Event_Engine_Factory::build($config);
    }


    public function testFineLoad()
    {
        $this->assertType('Edo_Event_Engine_Abstract', $this->engine);
    }

    public function testUnsupportedEngineType()
    {
        $this->setExpectedException('Edo_Event_Engine_Exception');
        $config = array();
        $config['type'] = 'moo';
        $moo =  Edo_Event_Engine_Factory::build($config);
    }

}


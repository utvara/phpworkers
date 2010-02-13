<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . "/TestConfiguration.php");

require_once WORKSHOP_ROOT . "/Edo/Event/Engine.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine/File.php";
require_once WORKSHOP_ROOT . "/Edo/Event.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine/Factory.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Worker/Manager.php";

class Edo_Event_Worker_ManagerTest extends PHPUnit_Framework_TestCase {

    protected $engine = null;
    protected $test_config = array();

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp ();
        $this->engine = FileEngineHelper::getNewFileEngine();
        Edo_Event_Engine::setDefaultEngine($this->engine);
        include(TESTS_ROOT . DIRECTORY_SEPARATOR . 'config-tests.php');
        $this->test_config = $config;
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        Edo_Event_Engine::setDefaultEngine(null);
        FileEngineHelper::revertSystem($this->engine);
        $this->engine = null;
        $this->test_config = array();
        parent::tearDown();
    }

    public function testProcessSingleEvent()
    {
        $data = array(
            'ref' => '/article/123/12121432',
            'event' => 'update',
            'tag' => array ('article','category/342','marker/432')
        );
        $id = Edo_Event_Factory::create($data);

        $event = $this->engine->findEventById($id,'manager');
        $lock = $this->engine->lock($id,'manager');
        $this->assertSame($lock,true);

        //lets process this message

        //now that all is as expected lets process the event and check if work pools go populated
        $worker = new Edo_Event_Worker_Manager($this->test_config);
        $worker->processEvent($event);

        $files = glob($this->engine->getDirPath('testworker') . DIRECTORY_SEPARATOR . '*');
        $this->assertEquals(count($files),1);

        //creating new one with fake tag..to make sure nothing new is created as files.

        $data = array(
            'ref' => '/article/123/12121432',
            'event' => 'update',
            'tag' => array ('blah')
        );
        $id = Edo_Event_Factory::create($data);
        $event = $this->engine->findEventById($id,'manager');
        $lock = $this->engine->lock($id,'manager');
        $this->assertSame($lock,true);

        $files = glob($this->engine->getDirPath('testworker') . DIRECTORY_SEPARATOR . '*');
        $this->assertEquals(count($files),1);

        $files = glob($this->engine->getDirPath('manager') . DIRECTORY_SEPARATOR . '*');
        $this->assertEquals(count($files),3);//two created + event_hardcoded
    }

    public function testAlternativeSyntaxOfUsFiltersANDSyntax()
    {
        $data = array(
            'ref' => '/article/123/12121432',
            'event' => 'update',
            'tag' => array ('article','article/123','marker','quad_key')
        );
        $id = Edo_Event_Factory::create($data);
        $event = $this->engine->findEventById($id,'manager');

        $worker = new Edo_Event_Worker_Manager($this->test_config);
        $this->assertTrue($worker->processEvent($event));

        $base = TESTS_ROOT . "/data";
        $files = glob("{$base}" . DIRECTORY_SEPARATOR . "*" . DIRECTORY_SEPARATOR . "pool" . DIRECTORY_SEPARATOR ."event_*");
        $this->assertTrue(count($files) == 4);
    }

    public function testAlternativeSyntaxOfUsFilters2()
    {
        $data = array(
            'ref' => '/article/123/12121432',
            'event' => 'update',
            'tag' => array ('article','article/123')
        );
        $id = Edo_Event_Factory::create($data);
        $event = $this->engine->findEventById($id,'manager');

        $worker = new Edo_Event_Worker_Manager($this->test_config);
        $this->assertTrue($worker->processEvent($event));

        $base = TESTS_ROOT . "/data";
        $files = glob("{$base}" . DIRECTORY_SEPARATOR . "*" . DIRECTORY_SEPARATOR . "pool" . DIRECTORY_SEPARATOR ."event_*");
        $this->assertTrue(count($files) == 3);
    }

    public function testGlobalConfigs()
    {
        $worker = new Edo_Event_Worker_Manager($this->test_config);

        $global = array();
        $global['foo'] = 'aaaaaa';
        $global['moo'] = 'bbbbbbb';
        $global['solr']['host'] = 'localhost';
        $global['solr']['port'] = '8080';
        $global['workers']['testworker']['foo'] = 'someinterestingstring';
        $global['workers']['manager']['active_workers'] = array('aaaaaaa');

        $worker->setGlobalConfig($global);
        $this->assertSame($global,$worker->getGlobalConfig());

        $worker->setGlobalConfig($global);

        $this->assertSame($global['solr']['host'],$worker->getGlobalConfigKey('solr.host'));//no default existent

        $this->assertSame(null,$worker->getGlobalConfigKey('solr.hostmooo'));//no default non existent
        $this->assertSame('33',$worker->getGlobalConfigKey('nonexistent.bee','33'));//default non-existent
        $this->assertSame('33',$worker->getGlobalConfigKey('nonexistent','33'));//default non-existent
        $this->assertSame($global['solr']['host'],$worker->getGlobalConfigKey('solr.host','33'));//default existent

        $worker->setGlobalConfigKey('moo','zzz');
        $foo = $global;
        $foo['moo'] = 'zzz';
        $this->assertSame($foo,$worker->getGlobalConfig());
        unset($foo);

        $worker->setGlobalConfig($global);

        $this->assertSame($global['workers']['manager']['active_workers'],$worker->getWorkerConfigKey('active_workers'));//no default existent
        $this->assertSame(null,$worker->getWorkerConfigKey('solr.hostmooo'));//no default non existent
        $this->assertSame('33',$worker->getWorkerConfigKey('mooo','33'));//default non-existent
        $this->assertSame($global['workers']['manager']['active_workers'],$worker->getWorkerConfigKey('active_workers','33'));//default existent

        $moo = $global;
        unset($moo['workers']['manager']);
        $worker->setGlobalConfig($moo);
        $this->assertNull($worker->getWorkerConfig());
        $worker->setWorkerConfig('moo');
        $this->assertSame('moo',$worker->getWorkerConfig());

    }
}


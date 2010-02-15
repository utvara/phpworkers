<?php


/**
 * phpworkers
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @copyright  Copyright (c) 2010 Slobodan Utvic and Julian Davchev
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once (dirname(dirname(dirname(__FILE__))) . "/TestConfiguration.php");

//require_once WORKSHOP_ROOT . "/Edo/Event.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Factory.php";

/**
 * Batch_Video test case.
 */
class Edo_Event_FactoryTest extends PHPUnit_Framework_TestCase {
    protected $engine = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        $this->engine = FileEngineHelper::getNewFileEngine();
        parent::setUp ();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        FileEngineHelper::revertSystem($this->engine);
        Edo_Event_Engine::setDefaultEngine(null);
        $this->engine = null;
        parent::tearDown ();
    }

    public function testCreateAllArgs()
    {
        $event_data = array (
            'ref' => '/yapeeeee/2',
            'event' => 'create',
            'tag' =>
            array (
                0 => 'article',
                1 => 'marker',
            ),
            'time_started' => 1248086540,
        );

        $id = Edo_Event_Factory::create($event_data,'manager',$this->engine);

        $event = $this->engine->findEventById($id,'manager');
        $this->assertEquals($id,$event->id);
    }

    public function testCreateDefaultEngine()
    {
        Edo_Event_Engine::setDefaultEngine($this->engine);
        $event_data = array (
            'ref' => '/yapeeeee/2',
            'event' => 'create',
            'tag' =>
            array (
                0 => 'article',
                1 => 'marker',
            ),
            'time_started' => 1248086540,
        );

        $id = Edo_Event_Factory::create($event_data,'manager');

        $isLocked = $this->engine->isLocked($id,'manager');
        $this->assertFalse($isLocked);

        $event = $this->engine->findEventById($id,'manager');
        $this->assertEquals($id,$event->id);

    }

    public function testCreateEventOrData()
    {
        Edo_Event_Engine::setDefaultEngine($this->engine);
        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;


        $id = Edo_Event_Factory::create($event,'manager');

        $isLocked = $this->engine->isLocked($id,'manager');
        $this->assertFalse($isLocked);

        $event = $this->engine->findEventById($id,'manager');
        $this->assertEquals($id,$event->id);
    }

    public function testAckquireLockArgument()
    {
        Edo_Event_Engine::setDefaultEngine($this->engine);
        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;


        $id = Edo_Event_Factory::create($event,'manager',null,true);

        $isLocked = $this->engine->isLocked($id,'manager');
        $this->assertTrue($isLocked);
    }

    public function testWrongInputCreatesExceptionCreatesDefaultEngine()
    {
        $this->setExpectedException('Edo_Event_Engine_Exception');
        Edo_Event_Engine::setDefaultEngine(null);
        $id = Edo_Event_Factory::create('moooo','manager',$this->engine,true);
    }
}


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
require_once (dirname(dirname(dirname(dirname(__FILE__)))) . "/TestConfiguration.php");

require_once WORKSHOP_ROOT . "/Edo/Event/Engine/File.php";
require_once WORKSHOP_ROOT . "/Edo/Event.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine/Factory.php";

/**
 * Batch_Video test case.
 */
class Edo_Event_Engine_FileTest extends PHPUnit_Framework_TestCase {

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

    public function testLoadConfiguration()
    {
        //not directory
        $config = array();
        $config['base_path'] = TESTS_ROOT . "/_files/fake";
        try {
            $this->_getNewInstance($config);
            $this->fail('An expected exception Edo_Event_Engine_Exception has not been raised.');
        } catch (Edo_Event_Engine_Exception $e) {
            $this->assertEquals($e->getMessage(),"base_path config is not readable directory");
        }

        $config = array();
        $config['base_path'] = "/data";
        try {
            $this->_getNewInstance($config);
            $this->fail('An expected exception Edo_Event_Engine_Exception has not been raised.');
        } catch (Edo_Event_Engine_Exception $e) {
            $this->assertEquals($e->getMessage(),"base_path config option should be fullpath");
        }

        //normal load
        $config = array();
        $config['base_path'] = TESTS_ROOT . "/data";
        try {
            $this->_getNewInstance($config);
        } catch (Edo_Event_Engine_Exception $e) {
            $this->fail('Did not expected exception');
        }
    }

    /**
     * Expecting base_path in configs
     */
    public function testBasePathExpected()
    {
        $this->setExpectedException('Edo_Event_Engine_Exception');
        $this->_getNewInstance(array());
    }

    public function testConfigIsArray()
    {
        $this->setExpectedException('Edo_Event_Engine_Exception');
        $this->_getNewInstance('mooooo');
    }

    public function testFindEventById()
    {
        $event = $this->engine->findEventById("event_hardcoded",'manager');
        $event_data = array (
            'id' => 'event_hardcoded',
            'ref' => '/marker/2',
            'event' => 'create',
            'body' => null,
            'lang' => null,
            'tag' =>
            array (
                0 => 'article',
                1 => 'marker',
            ),
            'time_started' => 1248086540,
            'attempts_made' => 0,
        );
        $this->assertEquals($event->toArray(),$event_data);

        $event = $this->engine->findEventById("noneexisting",'manager');
        $this->assertSame($event,null);
    }

    public function testCreate()
    {
        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;
        $newId = $this->engine->create('manager',$event);

        $event_data = array (
            'id' => $newId,
            'ref' => '/marker/2',
            'event' => 'create',
            'body' => null,
            'lang' => null,
            'tag' =>
            array (
                0 => 'article',
                1 => 'marker',
            ),
            'time_started' => $time_for_this_entry,
            'attempts_made' => 0
        );

        $event = $this->engine->findEventById($newId,'manager');
        $this->assertEquals($event_data, $event->toArray());


        $newId = $this->engine->unlock($event->id,'manager');
    }

    public function testDelete()
    {
        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;
        $newId = $this->engine->create('manager',$event);

        $event = $this->engine->findEventById($newId,'manager');
        $this->assertTrue(is_array($event->toArray()));

        $this->engine->delete($newId,'manager');

        $event = $this->engine->findEventById($newId,'manager');
        $this->assertTrue(is_null($event));
    }

    public function testUpdate()
    {
        //create new
        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;
        $newId = $this->engine->create('manager',$event);

        //check that it's locked
        $this->assertTrue($this->engine->isLocked($newId,'manager'));

        //update the same thingy
        $event = $this->engine->findEventById($newId,'manager');
        $event->ref = '/article/2';
        $res = $this->engine->update($event->id,'manager',$event);

        $this->assertTrue($res);

        //check that it's still locked
        $this->assertTrue($this->engine->isLocked($event->id,'manager'));

        $this->engine->unlock($event->id,'manager');

        $this->assertFalse($this->engine->isLocked($event->id,'manager'));

        //check if update is correct
        $event = $this->engine->findEventById($event->id,'manager');

        $this->assertEquals($event->ref,'/article/2');


        $this->engine->lock($event->id,'manager');

        //it should still be locked
        $this->assertTrue($this->engine->isLocked($event->id,'manager'));

        //unlock it
        $this->engine->unlock($event->id,'manager');
        $this->assertFalse($this->engine->isLocked($event->id,'manager'));
    }

    public function testLock()
    {
        $event = $this->engine->findEventById('event_hardcoded','manager');
        $isLocked = $this->engine->isLocked($event->id,'manager');
        $this->assertFalse($isLocked);

        $isLocked = $this->engine->lock($event->id,'manager');
        $this->assertTrue($isLocked);

        $success = $this->engine->unlock($event->id,'manager');
        $this->assertTrue($success);

        $isLocked = $this->engine->isLocked($event->id,'manager');
        $this->assertFalse($isLocked);
    }

    public function testCreateShouldLockRightAway()
    {
        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;
        $newId = $this->engine->create('manager',$event);


        $isLocked = $this->engine->isLocked($newId,'manager');
        $this->assertTrue($isLocked);

        $this->engine->unlock($newId,'manager');
    }

    public function testUpdateShouldExceptionIfNotLocked()
    {
        //should give this exception on the update....
        $this->setExpectedException('Edo_Event_Engine_Exception');

        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;
        $newId = $this->engine->create('manager',$event);

        $isLocked = $this->engine->isLocked($newId,'manager');
        $this->assertTrue($isLocked);
        $this->engine->unlock($newId,'manager');


        $event->ref = 'aaaaa';
        $this->engine->update($event->id,'manager',$event);
    }

    public function testGetFreeEvent()
    {
        $event = $this->engine->getFreeEvent('manager');
        $this->assertEquals($event->id,"event_hardcoded");

        //check if really locked
        $isLocked = $this->engine->isLocked($event->id,'manager');
        $this->assertTrue($isLocked);

        //no more free events
        $event = $this->engine->getFreeEvent('manager');
        $this->assertSame(null,$event);
    }

    public function testGetFreeEventAfterUnlock()
    {
        $event = $this->engine->getFreeEvent('manager');
        $this->assertEquals($event->id,"event_hardcoded");

        //we unlock the event that we just took
        $this->engine->unlock($event->id,'manager');

        //no more free events
        $event = $this->engine->getFreeEvent('manager');
        $this->assertEquals($event,false);
    }

    public function testExceptionOnFalseWorkerName()
    {
        //should give this exception on the update....
        $this->setExpectedException('Edo_Event_Engine_Exception');
        $event = $this->engine->findEventById("event_hardcoded",'wrong_worker');
    }

    public function testIncrementAttempts()
    {
        $time_for_this_entry = time();
        $event = new Edo_Event();
        $event->ref = "/marker/2";
        $event->event = 'create';
        $event->tag = array('article','marker');
        $event->time_started = $time_for_this_entry;
        $newId = $this->engine->create('manager',$event);

        $event = $this->engine->findEventById($newId,'manager');
        $this->assertEquals($event->attempts_made,0);

        $this->engine->incrementAttempts('manager',$event);
        $event = $this->engine->findEventById($newId,'manager');
        $this->assertEquals($event->attempts_made,1);

        $this->engine->incrementAttempts('manager',$event);
        $event = $this->engine->findEventById($newId,'manager');
        $this->assertEquals($event->attempts_made,2);
    }
}


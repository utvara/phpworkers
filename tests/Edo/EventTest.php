<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once (dirname(dirname(__FILE__)) . "/TestConfiguration.php");

require_once WORKSHOP_ROOT . "/Edo/Event.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Exception.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine/File.php";

/**
 *
 */
class Edo_EventTest extends PHPUnit_Framework_TestCase {

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp ();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        parent::tearDown ();
    }

    public function testCreating()
    {
        $event = new Edo_Event();
        $data = $event->toArray();
        unset($data['time_started']);
        $expected = array(
            'id' =>  null,
            'ref' => null,
            'event' => null,
            'tag' => null,
            'body' => null,
            'lang' => null,
            'attempts_made' => 0
        );
        $this->assertEquals($expected,$data);


        //create 2
        $input_data = array(
            'id' =>'some_id',
            'ref' => '/article/2'
        );
        $event = new Edo_Event($input_data);
        $data = $event->toArray();
        unset($data['time_started']);
        $expected = array(
            'id' =>  'some_id',
            'ref' => '/article/2',
            'event' => null,
            'tag' => null,
            'body' => null,
            'lang' => null,
            'attempts_made' => 0
        );
        $this->assertEquals($expected,$data);
    }

    public function testExceptionUnsupportedProperty()
    {
        $this->setExpectedException('Edo_Event_Exception');
        $event = new Edo_Event();
        $event->unsupported = 'test';
    }
}


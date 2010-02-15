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

require_once WORKSHOP_ROOT . "/Edo/Event.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine.php";
require_once WORKSHOP_ROOT . "/Edo/Event/Engine/Factory.php";

/**
 *
 */
class Edo_Event_EngineTest extends PHPUnit_Framework_TestCase {
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
        $this->engine = null;
        parent::tearDown ();
    }

    public function testsetGet()
    {
        $e = Edo_Event_Engine::getDefaultEngine();
        $this->assertSame($e,null);

        Edo_Event_Engine::setDefaultEngine($this->engine);

        $e = Edo_Event_Engine::getDefaultEngine();
        $this->assertSame($e,$this->engine);

        Edo_Event_Engine::setDefaultEngine(null);
    }
}


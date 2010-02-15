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

require_once (dirname(__FILE__) . "/Exception.php");

abstract class Edo_Event_Engine_Abstract
{
    protected $config = array();
    private $tried_events = array();

    public function __construct($config)
    {
        $config = $this->_checkRequiredOptions($config);
        $this->config = $config;
        $this->init();
    }

    /**
     * Checks if options passed are fine or not
     * May throw Edo_Event_Engine_Exception
     */
    abstract protected function _checkRequiredOptions($config);

    /**
     * Called after __construct is build. For initing object specific stuff
     */
    abstract protected function init();

    protected function isTriedEvent($id,$worker_name)
    {
        if (!isset($this->tried_events[$worker_name])) {
            return false;
        }

        return in_array($id,$this->tried_events[$worker_name]);
    }

    protected function markTriedEvent($id,$worker_name)
    {
        if (!isset($this->tried_events[$worker_name])) {
            $this->tried_events[$worker_name] = array();
        }
        $this->tried_events[$worker_name][] = $id;
    }

    abstract public function findEventById($id,$worker_name);

    abstract public function create($worker_name,Edo_Event $event);//no id needed....even if have one will be redundent
    abstract public function update($id,$worker_name, Edo_Event $event);//must have id
    abstract public function delete($id,$worker_name);
    abstract public function isLocked($id,$worker_name);
    abstract public function lock($id,$worker_name);
    abstract public function unlock($id,$worker_name);
    abstract public function incrementAttempts($worker_name, Edo_Event $event);
    abstract public function failed($id, $worker_name);
}

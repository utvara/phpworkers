<?php

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

    protected function isTriedEvent($id,$poolName)
    {
        if (!isset($this->tried_events[$poolName])) {
            return false;
        }

        return in_array($id,$this->tried_events[$poolName]);
    }

    protected function markTriedEvent($id,$poolName)
    {
        if (!isset($this->tried_events[$poolName])) {
            $this->tried_events[$poolName] = array();
        }
        $this->tried_events[$poolName][] = $id;
    }

    abstract public function findEventById($id,$poolName);

    abstract public function create($poolName,Edo_Event $event);//no id needed....even if have one will be redundent
    abstract public function update($id,$poolName, Edo_Event $event);//must have id
    abstract public function delete($id,$poolName);
    abstract public function isLocked($id,$poolName);
    abstract public function lock($id,$poolName);
    abstract public function unlock($id,$poolName);
    abstract public function incrementAttempts($poolName, Edo_Event $event);
    abstract public function failed($id, $poolName);
}

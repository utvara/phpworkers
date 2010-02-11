<?php

require_once(dirname(__FILE__) . '/Abstract.php');
require_once(dirname(dirname(__FILE__)) . '/Factory.php');//Edo_Event_Factory

class Edo_Event_Worker_Manager extends Edo_Event_Worker_Abstract
{

    protected $name = 'manager';

    private $filters = array();

    public function __construct()
    {
        parent::__construct();
    }

    protected function _loadFilters()
    {
        if (!$this->filters_path) {
            throw new Edo_Event_Worker_Exception("Could not find filters path for manager worker");
        }

        if (!is_dir($this->filters_path) || !is_readable($this->filters_path)) {
            throw new Edo_Event_Worker_Exception("Filter path not directory or not readable");
        }

        $allFilters = $this->getGlobalConfigKey('workers');
        $useFilters = $this->getWorkerConfigKey('active_workers');


        foreach ($allFilters as $name => $config) {
            if ($use_filters && $use_filters != 'all') {
                if (!in_array($name, $useFilters)) {
                    continue;
                }
            }
            $this->filters[$name] = $config;
        }
    }

    public function processEvent(Edo_Event $event)
    {
        $this->_loadFilters();
        foreach ($this->filters as $filter) {
            if ($this->hasMatch($filter,$event)) {
                $poolName = constant('Edo_Event_Poolable::'. $filter['pool']);
                $newEvent = clone $event;
                //TODO @utvara second argument should be worker_name now
                Edo_Event_Factory::create($newEvent, $poolName);
            } else {
                $e = var_export($event->toArray(),1);
                $f = var_export($filter,1);
//                $this->log("No match for filter $f  event $e");
            }
        }

        return true;
    }

    protected function hasMatch($filter,Edo_Event $event)
    {
        $event_name = $event->event;

        if (isset($filter['filter']) && $filter['filter'] == 'ACCEPT_ALL') {
            return true;
        }

        if (!isset($filter['filter'][$event_name])) {
            return false;
        }

        $ar1 = $filter['filter'][$event_name];
        $ar2 = $event->tag;

        //handling simple syntax
        if (!is_array($ar1) || !is_array($ar2)) {
            return false;
        }
        $diff = array_intersect($ar1,$ar2);
        $haveHit = !empty($diff);
        if ($haveHit) return true;

        //check if special syntax  there:   moo&foo
        foreach ($ar1 as $v) {
            if (strpos($v,'&') === FALSE) {
                continue;
            }
            $split = explode('&',$v);
            $diffSpecial = array_intersect($split,$ar2);
            $haveHit = ( $split == $diffSpecial );
            if ($haveHit) break;//if true hit..don't check the rest...
        }
        return $haveHit;
    }
}

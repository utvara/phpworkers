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

require_once(dirname(__FILE__) . '/Abstract.php');
require_once(dirname(dirname(__FILE__)) . '/Factory.php');//Edo_Event_Factory

class Edo_Event_Worker_Manager extends Edo_Event_Worker_Abstract
{

    protected $name = 'manager';

    private $filters = array();

    public function __construct($config)
    {
        parent::__construct($config);
    }

    protected function _loadFilters()
    {
        $all_filters = $this->getGlobalConfigKey('workers');
        $use_filters = $this->getWorkerConfigKey('active_workers');

        foreach ($all_filters as $name => $config) {
            if (!in_array($name, $use_filters)) {
                continue;
            }
            $this->filters[$name] = $config;
        }
    }

    public function processEvent(Edo_Event $event)
    {
        $this->_loadFilters();
        foreach ($this->filters as $worker_name => $filter) {
            if ($this->hasMatch($filter,$event)) {
                $newEvent = clone $event;
                Edo_Event_Factory::create($newEvent, $worker_name);
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

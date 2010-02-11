<?php

require_once(dirname(dirname(__FILE__)) .  "/Event.php");
require_once(dirname(__FILE__) .  "/Engine.php");
require_once(dirname(__FILE__) .  "/Engine/Factory.php");
require_once(dirname(__FILE__) .  "/Engine/Exception.php");

class Edo_Event_Factory
{
    public static function create($eventOrData,$worker_name,
        Edo_Event_Engine_Abstract $engine = null,$aquire_lock = false)
    {
        if (!$engine) {
            $engine = Edo_Event_Engine::getDefaultEngine();
        }

        if (!$engine) {
            throw new Edo_Event_Engine_Exception("Unable to create Edo_Event object");
        }

        if (is_array($eventOrData)) {
            $event = new Edo_Event($eventOrData);
        } elseif ($eventOrData instanceof Edo_Event) {
            $event = $eventOrData;
        } else {
            throw new Edo_Event_Engine_Exception("Unable to create Edo_Event object");
        }

        //TODO: move _pool magic to Engine
        $poolName = $worker_name . '_pool';
        if ($id = $engine->create($poolName,$event)) {
            if (!$aquire_lock) {
                $engine->unlock($event->id,$poolName);
            }
            return $id;
        }
    }
}

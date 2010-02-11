<?php

class Edo_Event_Engine
{
    static protected $engine = null;

    public static function setDefaultEngine(Edo_Event_Engine_Abstract $engine = null)
    {
        self::$engine = $engine;
    }

    public static function getDefaultEngine()
    {
        return self::$engine;
    }
}

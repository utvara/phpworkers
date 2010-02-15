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

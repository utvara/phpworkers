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

require_once(dirname(__FILE__). "/File.php");

class Edo_Event_Engine_Factory
{
    public static function build($config)
    {
        switch ($config['type']) {
        case 'file':
            return new Edo_Event_Engine_File($config);
            break;
        default:
            throw new Edo_Event_Engine_Exception("Unsupported engine type");
            break;
        }
    }
}

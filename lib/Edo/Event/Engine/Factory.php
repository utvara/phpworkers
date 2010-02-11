<?php

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

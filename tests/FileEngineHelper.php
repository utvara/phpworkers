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


class FileEngineHelper
{
    private static $dirs = array(
        'manager_pool'          => "/manager/pool",
        'manager_filters'       => "/manager/filters",
        'feedy_pool'            => "/feedy/pool"
    );

    public static function getNewFileEngine()
    {
        $config = array();
        $config['base_path'] = dirname(__FILE__) . "/data";
        $config['type'] = 'file';
        return Edo_Event_Engine_Factory::build($config);
    }

    public static function revertSystem(Edo_Event_Engine_File $engine)
    {
        $base = dirname(__FILE__) . "/data";
        $dirs = glob("{$base}"
                            . DIRECTORY_SEPARATOR . "*"
                            . DIRECTORY_SEPARATOR . Edo_Event_Engine_File::POOL_DIRECTORY_NAME ,GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            self::unlinkRecursive($dir);
        }
    }

    protected static function unlinkRecursive($dir, $deleteRootToo = false)
    {
        if(!$dh = opendir($dir))
        {
            throw new Exception ("Cannot open handler for dir {$dir}");
        }
        while (false !== ($obj = readdir($dh)))
        {
            if($obj == '.' || $obj == '..' || $obj == '.svn' || preg_match("#hardcoded#",$obj))
            {
                continue;
            }

            if (!unlink($dir . '/' . $obj))
            {
                self::unlinkRecursive($dir.'/'.$obj, true);
            }
        }

        closedir($dh);

        if ($deleteRootToo)
        {
            rmdir($dir);
        }
        return;
    }
}

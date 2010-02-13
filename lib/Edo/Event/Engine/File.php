<?php

require_once(dirname(__FILE__) . "/Abstract.php");

class Edo_Event_Engine_File extends Edo_Event_Engine_Abstract
{
    /**
     * Those constants are just used to be handy when doing testcases
     * @see getDirPath()
     */
    const POOL_DIRECTORY_NAME = 'pool';
    const FAILED_DIRECTORY_NAME = 'failed';
    const FILTERS_DIRECTORY_NAME = 'filters';

    protected $fp_locks = array();

    protected function init()
    {
    }

    protected function _checkRequiredOptions($config)
    {
        if (!is_array($config)) {
            throw new Edo_Event_Engine_Exception("Config should be array.");
        }

        //empty includes isset
        if (empty($config['base_path'])) {
            throw new Edo_Event_Engine_Exception("base_path config option required for File engine.");
        }

        if (realpath($config['base_path']) != $config['base_path']) {
            throw new Edo_Event_Engine_Exception("base_path config option should be fullpath");
        }

        if (!is_dir($config['base_path']) || !is_readable($config['base_path'])) {
            throw new Edo_Event_Engine_Exception("base_path config is not readable directory");
        }

        $config['base_path'] = rtrim($config['base_path'],'/');

        return $config;
    }

    /**
     * Used for testcases only...to validate things TODO rethink this approach
     */
    public function getDirPath($worker_name,$dirType = self::POOL_DIRECTORY_NAME)
    {
        $worker_name = trim($worker_name,DIRECTORY_SEPARATOR);
        $fullPath = $this->config['base_path']
            . DIRECTORY_SEPARATOR .  $worker_name
            . DIRECTORY_SEPARATOR . $dirType;

        if (!is_dir($fullPath) || !is_writable($fullPath)) {
            throw new Edo_Event_Engine_Exception("Unrecognised path $fullPath or directory not writable");
        }
        return $fullPath;
    }

    protected function _saveEvent($worker_name,Edo_Event $event,$isCreate = false)
    {
        $path = $this->getDirPath($worker_name);
        $event_path = $path . DIRECTORY_SEPARATOR . $event->id;
        $fp = $this->getFp($event->id,$worker_name);
        if (!$fp && !$isCreate) {
            throw new Edo_Event_Engine_Exception("No lock acquired on event {$event->id} in pool {$worker_name}. Update canceled;");
        } elseif($isCreate) {
            $fp = fopen($event_path, "w+");
            @chmod($event_path, 0666);
            if(flock($fp, LOCK_EX | LOCK_NB)) {
                $this->fp_locks[$this->getFpIndex($event->id,$worker_name)] = $fp;
            } else {
                throw new Edo_Event_Engine_Exception("Unable to acquire lock on creating new file {$event_path}");
            }
        }

        rewind($fp);
        ftruncate($fp,0);
        $bytesWritten = fwrite($fp,json_encode($event->toArray()));
        return (boolean) $bytesWritten;
    }

    public function findEventById($id,$worker_name)
    {
        $path = $this->getDirPath($worker_name);
        $event_path = $path . DIRECTORY_SEPARATOR . $id;
        if (!is_file($event_path) || !is_readable($event_path)) {
            return null;
        }

        $fp = $this->getFp($id,$worker_name);
        if (!$fp) {//create temporary fp and close after you read stuff
            $data = json_decode(file_get_contents($event_path),true);
            return new Edo_Event($data);
        }

        rewind($fp);
        $contents = '';
        while (!feof($fp)) {
            $contents .= fread($fp, 8192);
        }
        $contents = json_decode($contents,true);
        return new Edo_Event($contents);
    }

    protected function generateId($worker_name)
    {
        $path = $this->getDirPath($worker_name);
        $unique_path = tempnam($path,'event_' . time());
        return basename($unique_path);
    }

    public function create($worker_name,Edo_Event $event)
    {
        $event->id = $this->generateId($worker_name);
        $res =  $this->_saveEvent($worker_name, $event,true);
        if ($res) {
            return $event->id;
        }
        return $res;
    }

    /**
     * Locks should be handled outside update
     */
    public function update($id,$worker_name, Edo_Event $event)
    {
        $path = $this->getDirPath($worker_name);
        $full = $path . DIRECTORY_SEPARATOR . $id;
        if (!is_file($full) || !is_writable($full)) {
            return false;
        }
        return $this->_saveEvent($worker_name, $event);
    }

    /**
     * Locks should be handled outside delete
     */
    public function delete($id,$worker_name)
    {
        $path = $this->getDirPath($worker_name);

        $res =  @unlink($path . DIRECTORY_SEPARATOR . $id);
        if ($res) {
            unset($this->fp_locks[$this->getFpIndex($id,$worker_name)]);
            return true;
        }
        return false;
    }

    /**
     * Throws exception
     */
    public function isLocked($id,$worker_name)
    {
        $path = $this->getDirPath($worker_name);
        $full = $path . DIRECTORY_SEPARATOR . $id;
        if (!is_file($full) || !is_writable($full)) {
            throw new Edo_Event_Engine_Exception("No such file {$full}");
        }

        //this on purpose new fp...cause we need to check if locked or not!!!
        $fp = fopen($full, "r+");
        if(flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            return false;
        }
        fclose($fp);
        return true;
    }

    public function lock($id,$worker_name)
    {
        $path = $this->getDirPath($worker_name);
        $full = $path . DIRECTORY_SEPARATOR . $id;
        if (!is_file($full) || !is_writable($full)) {
            return false;
        }

        $fp = $this->getFp($id,$worker_name);
        if (!$fp) {
            $fp = fopen($full, "r+");
        }
        if(flock($fp, LOCK_EX | LOCK_NB)) {
            $this->fp_locks[$this->getFpIndex($id,$worker_name)] = $fp;
            return true;
        }
        return false;
    }

    protected function getFpIndex($id,$worker_name)
    {
        return md5($worker_name . $id);
    }

    protected function getFp($id,$worker_name)
    {
        $index = $this->getFpIndex($id,$worker_name);
        if (!isset($this->fp_locks[$index])) {
            return null;
        }
        return $this->fp_locks[$index];
    }


    public function unlock($id,$worker_name)
    {
        $fp = $this->getFp($id,$worker_name);
        if ($fp) {
            fclose($fp);
            unset($this->fp_locks[$this->getFpIndex($id,$worker_name)]);
            return true;
        }
        return false;
    }

    public function getFreeEvent($worker_name)
    {
        $path = $this->getDirPath($worker_name);

        $path = new DirectoryIterator($path);
        foreach ($path as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (strpos($file->getFilename(),'event_') !== 0) {//if it starts with
                continue;
            }

            $id = $file->getFilename();
            if ($this->isTriedEvent($id,$worker_name)) {
                continue;
            }

            if ($file->getSize() == 0) {
                continue;
            }

            if (!$this->isLocked($id,$worker_name)) {
                $this->lock($id,$worker_name);
                $event =  $this->findEventById($id,$worker_name);
                $this->markTriedEvent($id,$worker_name);
                return $event;
            }
        }
    }

    public function incrementAttempts($worker_name, Edo_Event $event)
    {
        $currentAttempts = isset($event->attempts_made) ? (int)$event->attempts_made : 0;
        $currentAttempts++;
        $event->attempts_made = $currentAttempts;
        return $this->update($event->id,$worker_name,$event);
    }

    public function failed($id,$worker_name)
    {
        $event_path = $this->getDirPath($worker_name) . DIRECTORY_SEPARATOR . $id;

        $failed = $this->getDirPath($worker_name,self::FAILED_DIRECTORY_NAME);
        $failed_path = $failed . DIRECTORY_SEPARATOR . $id;

        if (!is_dir($failed) || !is_writable($failed)) {
            throw new Edo_Event_Engine_Exception("Failed path for pool name: $pool   path: $failed  is not directory and or is not writable");
        }

        $res =  @rename($event_path,$failed_path);
        if (!$res) {
            throw new Edo_Event_Engine_Exception("Unable to rename $event_path to $failed_path");
        }
    }
}


<?php

require_once(dirname(__FILE__) . "/Abstract.php");

class Edo_Event_Engine_File extends Edo_Event_Engine_Abstract
{

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

        if (!is_dir($config['base_path']) || !is_writable($config['base_path'])) {
            throw new Edo_Event_Engine_Exception("base_path config option should be writtable directory");
        }

        $config['base_path'] = rtrim ($config['base_path'],'/');

        return $config;
    }

    public function getFailedDirPath($dirType)
    {
        $dirType = str_replace('pool','failed',$dirType);
        $dirType = str_replace('_',DIRECTORY_SEPARATOR,$dirType);
        $dirType = trim($dirType,DIRECTORY_SEPARATOR);
        $fullPath = $this->config['base_path'] . DIRECTORY_SEPARATOR .  $dirType;

        if (!is_dir($fullPath) || !is_writable($fullPath)) {
            throw new Edo_Event_Engine_Exception("Unrecognised path $fullPath or directory not writable");
        }
        return $fullPath;
    }

    public function getDirPath($dirType)
    {
        $dirType = str_replace('_',DIRECTORY_SEPARATOR,$dirType);
        $dirType = trim($dirType,DIRECTORY_SEPARATOR);
        $fullPath = $this->config['base_path'] . DIRECTORY_SEPARATOR .  $dirType;

        if (!is_dir($fullPath) || !is_writable($fullPath)) {
            throw new Edo_Event_Engine_Exception("Unrecognised path $fullPath or directory not writable");
        }
        return $fullPath;
    }

    protected function _saveEvent($poolName,Edo_Event $event,$isCreate = false)
    {
        $path = $this->getDirPath($poolName);
        $event_path = $path . DIRECTORY_SEPARATOR . $event->id;
        $fp = $this->getFp($event->id,$poolName);
        if (!$fp && !$isCreate) {
            throw new Edo_Event_Engine_Exception("No lock acquired on event {$event->id} in pool {$poolName}. Update canceled;");
        } elseif($isCreate) {
            $fp = fopen($event_path, "w+");
            @chmod($event_path, 0666);
            if(flock($fp, LOCK_EX | LOCK_NB)) {
                $this->fp_locks[$this->getFpIndex($event->id,$poolName)] = $fp;
            } else {
                throw new Edo_Event_Engine_Exception("Unable to acquire lock on creating new file {$event_path}");
            }
        }

        rewind($fp);
        ftruncate($fp,0);
        $bytesWritten = fwrite($fp,json_encode($event->toArray()));
        return (boolean) $bytesWritten;
    }

    public function findEventById($id,$poolName)
    {
        $path = $this->getDirPath($poolName);
        $event_path = $path . DIRECTORY_SEPARATOR . $id;
        if (!is_file($event_path) || !is_readable($event_path)) {
            return null;
        }

        $fp = $this->getFp($id,$poolName);
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

    protected function generateId($poolName)
    {
        $path = $this->getDirPath($poolName);
        $unique_path = tempnam($path,'event_' . time());
        return basename($unique_path);
    }

    public function create($poolName,Edo_Event $event)
    {
        $event->id = $this->generateId($poolName);
        $res =  $this->_saveEvent($poolName, $event,true);
        if ($res) {
            return $event->id;
        }
        return $res;
    }

    /**
     * Locks should be handled outside update
     */
    public function update($id,$poolName, Edo_Event $event)
    {
        $path = $this->getDirPath($poolName);
        $full = $path . DIRECTORY_SEPARATOR . $id;
        if (!is_file($full) || !is_writable($full)) {
            return false;
        }
        return $this->_saveEvent($poolName, $event);
    }

    /**
     * Locks should be handled outside delete
     */
    public function delete($id,$poolName)
    {
        $path = $this->getDirPath($poolName);

        $res =  @unlink($path . DIRECTORY_SEPARATOR . $id);
        if ($res) {
            unset($this->fp_locks[$this->getFpIndex($id,$poolName)]);
            return true;
        }
        return false;
    }

    /**
     * Throws exception
     */
    public function isLocked($id,$poolName)
    {
        $path = $this->getDirPath($poolName);
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

    public function lock($id,$poolName)
    {
        $path = $this->getDirPath($poolName);
        $full = $path . DIRECTORY_SEPARATOR . $id;
        if (!is_file($full) || !is_writable($full)) {
            return false;
        }

        $fp = $this->getFp($id,$poolName);
        if (!$fp) {
            $fp = fopen($full, "r+");
        }
        if(flock($fp, LOCK_EX | LOCK_NB)) {
            $this->fp_locks[$this->getFpIndex($id,$poolName)] = $fp;
            return true;
        }
        return false;
    }

    protected function getFpIndex($id,$poolName)
    {
        return md5($poolName . $id);
    }

    protected function getFp($id,$poolName)
    {
        $index = $this->getFpIndex($id,$poolName);
        if (!isset($this->fp_locks[$index])) {
            return null;
        }
        return $this->fp_locks[$index];
    }


    public function unlock($id,$poolName)
    {
        $fp = $this->getFp($id,$poolName);
        if ($fp) {
            fclose($fp);
            unset($this->fp_locks[$this->getFpIndex($id,$poolName)]);
            return true;
        }
        return false;
    }

    public function getFreeEvent($poolName)
    {
        $path = $this->getDirPath($poolName);

        $path = new DirectoryIterator($path);
        foreach ($path as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (strpos($file->getFilename(),'event_') !== 0) {//if it starts with
                continue;
            }

            $id = $file->getFilename();
            if ($this->isTriedEvent($id,$poolName)) {
                continue;
            }

            if ($file->getSize() == 0) {
                continue;
            }

            if (!$this->isLocked($id,$poolName)) {
                $this->lock($id,$poolName);
                $event =  $this->findEventById($id,$poolName);
                $this->markTriedEvent($id,$poolName);
                return $event;
            }
        }
    }

    public function incrementAttempts($poolName, Edo_Event $event)
    {
        $currentAttempts = isset($event->attempts_made) ? (int)$event->attempts_made : 0;
        $currentAttempts++;
        $event->attempts_made = $currentAttempts;
        return $this->update($event->id,$poolName,$event);
    }

    public function failed($id,$poolName)
    {
        $event_path = $this->getDirPath($poolName) . DIRECTORY_SEPARATOR . $id;

        $failed = $this->getFailedDirPath($poolName);
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


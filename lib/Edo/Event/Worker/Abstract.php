<?php


require_once(dirname(dirname(__FILE__)) . '/Engine/Abstract.php');
require_once(dirname(__FILE__) . '/Exception.php');

abstract class Edo_Event_Worker_Abstract
{
    //used to dermine path name of this worker and other stuff
    protected $name = null;
    private $global_config = array();

    protected $current_event = array();
    private $RestClient = null;//this is for lazy load only. client is used with $this->RestClient->...

    abstract public function processEvent(Edo_Event $event);

    public function __construct($config = null)
    {
        if (!$config) {
            $config_path = dirname(dirname(dirname(dirname(__FILE__)))) . "/data/config/";
            $config_file = $config_path .'config-local.php';
            $deprecated_config_file = $config_path . 'config.php';

            if (file_exists($config_file) && include($config_file)) {
            } else {
                if (!include($deprecated_config_file)) {
                    throw new Edo_Event_Worker_Exception("Fallback to deprecated config file failed. file: {$config_file}");
                }
            }

            if (file_exists($deprecated_config_file)) {
                trigger_error("$deprecated_config_file is deprecated. Should use config-local.php now",E_USER_NOTICE);
            }
        }
        $this->setGlobalConfig($config);
        $this->init();
    }


    protected function init()
    {

    }

    /**
     * Get configs
     */
    public function getGlobalConfig() {
        return $this->global_config;
    }

    public function getGlobalConfigKey($key,$default = null) {
        if (is_null($default) &&  !array_key_exists($key,$this->global_config)) {
            throw new Edo_Event_Worker_Exception("Worker {$this->name} Undefined  key {$key}");
        }

        return array_key_exists($key,$this->global_config) ? $this->global_config[$key] : $default;
    }

    public function getWorkerConfigKey($key,$default = null)
    {
        $config = (array) $this->getWorkerConfig();
        if (is_null($default) && !array_key_exists($key,$config)) {
            throw new Edo_Event_Worker_Exception("Worker {$this->name} Undefined  key {$key} in worker config");
        }
        return array_key_exists($key,$config) ? $config[$key] : $default;
    }

    public function getWorkerConfig($default = null)
    {
        if (is_null($default) && !array_key_exists($this->name,$this->global_config)) {
            throw new Edo_Event_Worker_Exception("Worker {$this->name} Undefined  worker config for worker {$this->name}");
        }
        return array_key_exists($this->name,$this->global_config) ? $this->global_config[$this->name] : $default;
    }


    /**
     * Set configs
     */

    public function setGlobalConfig($global_config) {
        $this->global_config = $global_config;
    }

    public function setGlobalConfigKey($key,$value) {
        $this->global_config[$key] = $value;
    }

    public function setWorkerConfig($config)
    {
        $this->global_config[$this->name] = $config;
    }

    public function setWorkerConfigKey($key,$value)
    {
        $this->global_config[$this->name][$key] = $value;
    }

    protected function log($message)
    {
        if (!$this->name) {
            return false;
        }

        $dir = dirname(dirname(dirname(dirname(__FILE__)))) . "/data/{$this->name}/log";
        if (!is_writable($dir) || !is_dir($dir)) {
            return false;
        }

        $tmp = debug_backtrace();
        $trace = array_shift($tmp);

        $file = $this->name . '.' .date('Ymd') . '.log';

        $date = date('Y-M-d H:i:s');
        $log = '[' . $date . ']';
        $log .= ' ';
        $log .= ' ' . $message . ' in ' . $trace['file'] . ' on line ' . $trace['line'];

        error_log($log. PHP_EOL,3,$dir . DIRECTORY_SEPARATOR . $file);
    }
}

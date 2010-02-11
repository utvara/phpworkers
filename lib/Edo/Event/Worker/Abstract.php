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

    public function __construct($config)
    {
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
        if (is_null($default) && !array_key_exists($this->name,$this->global_config['workers'])) {
            throw new Edo_Event_Worker_Exception("Worker {$this->name} Undefined  worker config for worker {$this->name}");
        }
        return array_key_exists($this->name,$this->global_config['workers']) ? $this->global_config['workers'][$this->name] : $default;
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
        $this->global_config['workers'][$this->name] = $config;
    }

    public function setWorkerConfigKey($key,$value)
    {
        $this->global_config['workers'][$this->name][$key] = $value;
    }

//    TODO revise how logging will occur
//    protected function log($message)
//    {
//        if (!$this->name) {
//            return false;
//        }

//        $dir = dirname(dirname(dirname(dirname(__FILE__)))) . "/data/{$this->name}/log";
//        if (!is_writable($dir) || !is_dir($dir)) {
//            return false;
//        }

//        $tmp = debug_backtrace();
//        $trace = array_shift($tmp);

//        $file = $this->name . '.' .date('Ymd') . '.log';

//        $date = date('Y-M-d H:i:s');
//        $log = '[' . $date . ']';
//        $log .= ' ';
//        $log .= ' ' . $message . ' in ' . $trace['file'] . ' on line ' . $trace['line'];

//        error_log($log. PHP_EOL,3,$dir . DIRECTORY_SEPARATOR . $file);
//    }
}

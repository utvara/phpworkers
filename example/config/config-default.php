<?php
//CREATE ONE config-local.php file where this file is suited and set variables specific to your environment
//you can set worker specific configs like $config['feedy']['something'] = 'a'
//where feedy is $this->name propertyof worker .
//
//Getting/Setting Global config...        get/set analogic
//   public function getGlobalConfigKey($key)
//   public function getGlobalConfig($key)


//Getting/settting WORKER config... get/set analogic
//
// public function getWorkerConfigKey($key)
// public function getWorkerConfig()


$config = array();
$config['env']['date_default_timezone_set'] = 'Europe/Berlin';//format like date_default_timezone_set()
$config['env']['error_reporting'] = E_ALL | E_STRICT ;//format for error_reporting();

$config['engine'] = array();
$config['engine']['type'] = "file";
$config['engine']['base_path'] = dirname(dirname(dirname(__FILE__))) . '/example/data';//full path to workers data/ dir


$config['workers'] = array();
$config['workers']['manager'] = array(
  "active_workers" => array("example", "eventstats"),
  "className" => "Edo_Event_Worker_Manager"
);

$config['workers']['eventstats'] = array(
  "filter" => 'ACCEPT_ALL',
  "className" => "My_Eventstats",
  "pathName" => dirname(dirname(__FILE__)) . '/workshop/Eventstats.php' 
);

$config['workers']['my_worker'] = array(
  "filter" => array("create" => array("article","marker"), "update" => array("article","marker")),
  "className" => "My_Worker",
  "pathName" => dirname(dirname(__FILE__)) . '/workshop/My_Worker.php'
);

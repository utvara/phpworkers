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
$config['engine']['lock_path'] = dirname(dirname(dirname(__FILE__))) . '/example/cronjob_lock';//full path to where full-lock files will be stored


/**
 * Each worker should 'class_name' and 'class_path' to find this class
 * Each class should be of type Edo_Event_Worker_Abstract
 * For built in workers (starting with "Edo_") - probably just manager will ever be) you need class_name only
 */
$config['workers'] = array();
$config['workers']['manager'] = array(
  "active_workers" => array("example", "eventstats",'myworker'),
  //with "Edo_" classes you don't need to supply path as it assumes it's a built in worker.
  "class_name" => "Edo_Event_Worker_Manager"
);

$config['workers']['eventstats'] = array(
  "filter" => 'ACCEPT_ALL',
  "class_name" => "My_Eventstats",
  "class_path" => dirname(dirname(__FILE__)) . '/workshop/Eventstats.php'
);

$config['workers']['myworker'] = array(
  "filter" => array("create" => array("article","marker"), "update" => array("article","marker")),
  "class_name" => "My_Worker",
  "class_path" => dirname(dirname(__FILE__)) . '/workshop/My_Worker.php'
);

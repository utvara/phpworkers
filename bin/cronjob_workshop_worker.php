<?php

/**
 * THIS IS CRONJOB
 * It runs either as manager or one of workers types. See docs/README.txt for more
 *
 * Syntax:
 * php ./cronjob_workshop_worker --worker=<worker_type>
 *
 * Example run of cronjob
 * php ./cronjob_workshop_worker.php --worker=manager --time-limit=20 --set-time-limit=0 --full-lock=true
 */

if (PHP_SAPI != 'cli') {
    exit("This is command line cronjob.");
}

//processing args
$args = $_SERVER['argv'];
$num_args = $_SERVER['argc'];
/**
 * Pass like
 * php example/cronjob_workshop_worker.php --worker=manager --time-limit=2032 --set-time-limit=0 --full-lock=true
 */
$defaultOptions = array(
    //this option is required and should be passed either explicitly or via setting environment variable EDO_WORKERS_BASE_CONFIG
    'base-config' => false,
    //this option is required and should be passed explicitly
    'worker' => false,
    //If true will not start another instance of the worker
    'full-lock' => false,
    //In seconds If lock is still there after this time expires
    //it will start complaining. Only useful if --full-lock=true
    'full-lock-warning-time' => 60*60,
    //In seconds After this period expires
    //if currently executed worker is still active (e.g heavy work on single event or going for lots of events)
    //it will force a stop. Helpful not to pile memory leaks for example.
    'time-limit' => 3 * 60,
    //number of allowed attempts before event is marked as failed.
    'allowed-attempts' => 100,
    //set_time_limit()  0 is for endless
    //In case of file engine means moving to failed/ directory
    'set-time-limit' => 0,
);
$options = parseOptions($num_args,$args,$defaultOptions);
require_once($options['base-config']);

//Setting environment stuff
error_reporting($config['env']['error_reporting']);
set_time_limit($options['set-time-limit']);
if (!empty($config['env']['date_default_timezone_set'])) {
    date_default_timezone_set($config['env']['date_default_timezone_set']);
}
set_include_path(implode(PATH_SEPARATOR,array(
    dirname(dirname(__FILE__)) . '/lib',
    get_include_path()
)));

require_once ("Edo/Event/Engine/Factory.php");
require_once ("Edo/Event.php");
require_once ("Edo/Event/Engine.php");
//require_once ("Edo/Event/Poolable.php");

//config
//$allowedWorkerTypes = array('manager','feedy', 'loggy', 'tweety', 'tiledrop',
//    'alldestination','sitemapy','contentstats','solrybulk',
//    'domestos','streamy', 'sanitar','solrysingle','eventstats','activity','eventloggy',
//    's3', 'imaginator', 'imageindexer', 'followrecommended','userindexer'
//);

if ($num_args < 2) {
    help();exit;
}

//validation of args
//$worker_type = $args[1];
//if (!in_array($worker_type,$allowedWorkerTypes)) {
//    help();exit;
//}

$worker_type = $options['worker'];
//var_dump($worker_type);
//exit;

$fullLock = $options['full-lock'];
$fullLock = $fullLock ? "lock_full_{$worker_type}" : null;
$fullLockWarningTime = $options['full-lock-warning-time'];
$allowedAttempts = $options['allowed-attempts'];
$limit_time = $options['time-limit'];
//create the worker based on worker_type...set the limit

//able to include custom workers and all
$className = "Edo_Event_Worker_" . ucfirst($worker_type);
$pathName = "Edo/Event/Worker/" . ucfirst($worker_type) . ".php";
require_once ($pathName);
//TODO pass filters directory for manager ...this consturct argument is for manager only........
$worker = new $className(dirname(__FILE__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . 'manager' . DIRECTORY_SEPARATOR . 'filters');
//TODO autodetermine pooll...no more Poolable $pool = Edo_Event_Poolable::POOL_WORKER_EVENTSTATS;
//
exit('aaaaa');

//check full locks...if worker is supposed to start at all
$fullLockPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cronjob_tmp'. DIRECTORY_SEPARATOR . $fullLock;
if ($fullLock) {
    if (is_file($fullLockPath)) {
        $lock_time = file_get_contents($fullLockPath);
        $currentLockTime = time() - $lock_time;
        if ($currentLockTime > $fullLockWarningTime) {
            echo "Full lock of worker {$worker_type} is not released for {$currentLockTime} seconds while the warning time is {$fullLockWarningTime} seconds. Might wanna have a look wassup." . PHP_EOL;
        }
        exit;//file is locked....some other worker of this type already working
    } else {//ackuire full lock
        file_put_contents($fullLockPath,time());
    }
}

//creating engine
$engine = Edo_Event_Engine_Factory::build($config['engine']);
Edo_Event_Engine::setDefaultEngine($engine);

//start timer
$start_time = time();
$elapsed_time = 0;

do {
    try {
        $failed = false;
        //check elapsed time
        $elapsed_time = time() - $start_time;
        if ($elapsed_time > $limit_time) {
            break;
        }

        $event = $engine->getFreeEvent($pool);
        if (!$event) {
            break;//no event to preocess...over
        }

        $engine->incrementAttempts($pool,$event);
        if ($event->attempts_made > $allowedAttempts) {
            echo "Worker $worker_type event_id: {$event->id} exceeded allowed attempts. Attempts {$event->attempts_made} Allowed: {$allowedAttempts}." . PHP_EOL;
            $engine->failed($event->id,$pool);
            $failed = true;
        }


        $allOk = false;
        if (!$failed) {
            $allOk = $worker->processEvent($event);
        }
        if ($allOk && !$failed) {
            $engine->delete($event->id, $pool);
        } else {
            $engine->unlock($event->id,$pool);
            //            $failed = true;
            //            echo "Worker $worker_type   event_id: {$event->id} unable to complete task. Tried {$event->attempts_made} times. Allowed attempts: {$allowedAttempts}" . PHP_EOL;
            //            if ($fullLock) {//worker is getting next work right away....so just updates the timeer in lock file
            //                file_put_contents($fullLockPath,time());
            //            }
            //            continue;
        }

        if ($fullLock) {//worker is getting next work right away....so just updates the timeer in lock file
            file_put_contents($fullLockPath,time());
        }

        if ($failed) {
            break;
        }
    } catch (Exception $e) {
        echo "Exception while processing event. File: {$e->getFile()} Line: {$e->getLine()} Exception: " . $e->getMessage() . PHP_EOL;
    }
} while (true);

//release full lock so next worker can start
if ($fullLock) {
    unlink($fullLockPath);
}

function help()
{
    global $allowedWorkerTypes;
    echo  "SYNTAX: php ./cronjob_workshop_worker <worker_type>" . PHP_EOL;
    echo  "Supported workers: ".implode($allowedWorkerTypes,'|'). "". PHP_EOL;
}

function parseOptions($num_options,$options,$defaultOptions)
{
    $final_options = array();

    foreach ($options as $key => $option_candidate) {
        if ($key == 0) continue;

        $explode = explode('=',$option_candidate);
        if (count($explode) < 2) {
            continue;
        }
        $option_candidate_raw = ltrim(array_shift($explode),'-');
        $option_candidate_value = implode('=',$explode);

        if (!array_key_exists($option_candidate_raw,$defaultOptions)) {
            echo "WARNING: Unrecognised option {$option_candidate_raw}" .PHP_EOL;
            continue;
        }
        if (in_array($option_candidate_value,array("true","false"))) {
            $final_options[$option_candidate_raw] = $option_candidate_value == "true"  ? true : false;
        } else {
            $final_options[$option_candidate_raw] = $option_candidate_value;
        }
    }
    $final_options = array_merge($defaultOptions,$final_options);
    if ($final_options['worker'] === false) {
        echo "ERROR parsing options. Required argument --worker=[worker_name] not passed" . PHP_EOL;
        exit;
    }

    if ($final_options['base-config'] === false) {
        if(!isset($_ENV['EDO_WORKERS_BASE_CONFIG'])) {
            echo "ERROR parsing options. Required argument --base-config=[path/to/config] not passed and EDO_WORKERS_BASE_CONFIG environment variable not set" . PHP_EOL;
            exit;
        }
        $final_options['base-config'] = $_ENV['EDO_WORKERS_BASE_CONFIG'];
    }
    return $final_options;
}


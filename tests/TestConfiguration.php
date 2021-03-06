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

date_default_timezone_set('Europe/Berlin');
require_once(dirname(__FILE__) . "/FileEngineHelper.php");
error_reporting(E_ALL | E_STRICT);

//different defines goes in here
//if (!defined('ERROR_LOG_FILE')) {
//    define('ERROR_LOG_FILE',dirname(__FILE__) . '/log/error.log');
//}

define('TESTS_GENERATE_REPORT', false);
define('TESTS_GENERATE_REPORT_TARGET', '/path/to/target');


define('TESTS_ROOT', dirname(__FILE__));
define('WORKSHOP_ROOT', dirname(dirname(__FILE__)) . '/lib');

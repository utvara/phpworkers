<?php

require_once(dirname(__FILE__) . '/Abstract.php');

class My_Eventstats extends Edo_Event_Worker_Abstract
{

    protected $name = 'eventstats';

    public function processEvent(Edo_Event $event)
    {
        $todayLog = 'eventstats.'.date('Ymd');
        $log = realpath($this->getWorkerConfigKey('eventstats_base_dir')) . DIRECTORY_SEPARATOR . $todayLog;
        $res =  @file_put_contents($log,$this->_formatEvent($event),FILE_APPEND);
        if ($res === false) {
            return false;
        }
        return true;
    }

    private function _formatEvent($event)
    {
        $line = '';
        $line .= $this->getEntity($event->ref) . ' ||| ';
        $line .= $event->event. ' ||| ';
        $line .= $event->lang. ' ||| ';
        $line .= $event->ref. ' ||| ';
        $line .= $event->time_started. ' ||| ';
        $line .= date('Y-m-d H:i:s',$event->time_started) . ' ||| ';
        $line .= json_encode($event->tag) . ' ||| ';
        $line .= json_encode($event->body) . '';
        $line .= PHP_EOL;
        return $line;
    }
}

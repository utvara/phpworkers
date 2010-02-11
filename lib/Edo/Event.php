<?php

require_once(dirname(__FILE__) . "/Event/Exception.php");

class Edo_Event
{

    protected $event_data = array();

    public function __construct($data = array())
    {
        if (!empty($data)) {
            if (isset($data['id'])) {
                $this->id = $data['id'];
            }
            if (isset($data['ref'])) {
                $this->ref = $data['ref'];
            }

            if (isset($data['event'])) {
                $this->event = $data['event'];
            }

            if (isset($data['tag'])) {
                $this->tag = $data['tag'];
            }

            if (isset($data['time_started'])) {
                $this->time_started = $data['time_started'];
            }

            if (isset($data['body'])) {
                $this->body = $data['body'];
            }

            if (isset($data['lang'])) {
                $this->lang = $data['lang'];
            }

            if (isset($data['attempts_made'])) {
                $this->attempts_made = $data['attempts_made'];
            }
        }
        if (!isset($this->time_started)) {
            $this->time_started = time();
        }

        if (!isset($this->attempts_made)) {
            $this->attempts_made = 0;
        }
    }

    /**
     * Row from `events` table
     * array (
         'id' => '32',
         'event_id' => '',
         'ref' => '/marker/3114/1258118758',
         'event' => 'update',
         'tag' => '["marker","marker\\/3114"]',
         'time_started' => '2009-11-13 14:25:58',
         'body' => 'null',
         'lang' => 'de',
     )
     */
    public static function buildFromDbRow($row)
    {
        $input = array(
            'id' => $row['id'],
            'ref' => $row['ref'],
            'event' => $row['event'],
            'tag' => empty($row['tag']) ? null : json_decode($row['tag'],true),
            'time_started' => strtotime($row['time_started']),
            'body' => empty($row['body']) ? null : json_decode($row['body'],true),
            'lang' => $row['lang'],
        );

        return new Edo_Event($input);
    }

    public function toArray()
    {
        //we explicitly create it cause using $event directly might not contain one of indexes
        return array(
            "id" => $this->id,
            "ref" => $this->ref,
            "event" => $this->event,
            "tag" => $this->tag,
            "time_started" => $this->time_started,
            "body" => $this->body,
            "lang" => $this->lang,
            "attempts_made" => $this->attempts_made,
        );
    }

    protected function getAllowedEventProperties()
    {
        return array('id','ref','event','tag','time_started','body','lang','attempts_made');
    }

    public function __get($name) {
        if (!in_array($name,$this->getAllowedEventProperties())) {
            throw new Edo_Event_Exception ("Unrecognised event property {$name}");
        }
        if (array_key_exists($name, $this->event_data)) {
            return $this->event_data[$name];
        }
        return null;
    }

    public function __isset($name) {
        if (!in_array($name,$this->getAllowedEventProperties())) {
            throw new Edo_Event_Exception ("Unrecognised event property {$name}");
        }
        return isset($this->event_data[$name]);
    }

    public function __set($name, $value) {
        if (!in_array($name,$this->getAllowedEventProperties())) {
            throw new Edo_Event_Exception ("Unrecognised event property {$name}");
        }
        $this->event_data[$name] = $value;
    }
}

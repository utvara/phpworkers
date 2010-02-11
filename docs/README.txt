Read top to bottom

######## WORKSHOP
API creates event(file) in /collective_pool    or worker creates file(event) in there.
timestamp.json
{
    "ref": "/article/123/12121432",
    "event": "update",
    "tag":["article"]
}

----------This is one cycle-------------work can be broken at this point----------
Worker manager scans /colective_pool (cronjob, or daemon) 

Once it find the message it checks who is interested in:
He checks filter templates like this one
scan /work_orders
Find:
{
    "pool": "/sorty_pool",
    "filter":[
        {"event":"create", "tag":["article", "marker"]},
        {"event":"update", "tag":["article", "marker"]}
    }
}


In this case message should be copied to /sporty_pool from /collective_pool cause it's update and it's article tag match
Worker manager deletes message from /colective_pool

----------This is one cycle-------------work can be broken at this point----------


Different worker cronjobs a run at different intervals. Example sorty
It checks /sorty_pool if there is work and grabs the first one in list.
reads a message
puts a flock on the message
do something (optionally can log some stuff in workshop/log/sorty.[current_day_date].log)
(optionally create new message in /collective_pool    like   sorty complete...if someone else later is interested in this)
- probably will create event for jobs done....so that manager probably assigns to work_stats worker for stats purposes.
remove flock
remove file

----------This is one cycle-------------work can be broken at this point----------


## MESSAGE FORMATS ##

1. event messages format  that are stored in /collective_pool:
- will be files for now.... format json(?array)

Fields:
* ref - <single value> - what entity was affected. Example  "/article/123/12121432"    entity, id, revision
* event -<single value> Event name.    Example update|create|delete|indexing_complete|deleteCache.......    will think in progresss of type of events and their impact
* "tag": <multi value> What other entities related to this event (bubble effect)      Example:   "article","marker"

2. filter template format
Field:
* pool - which pool will accept the message(menager should copy message in there if filter passes)
* filter - 
     [event_name]<single value>  - [tag]<multi values>    Example     {"event":"create", "tag":["article", "marker"]},
     
######## END WORKSHOP

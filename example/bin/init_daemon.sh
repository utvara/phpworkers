#!/bin/sh
# Quick start-stop-daemon example, derived from Debian /etc/init.d/ssh
set -e
# Must be a valid filename

NAME=$2;
if [ "$NAME" != "manager"  \
    -a "$NAME" != 'feedy'  \
    -a "$NAME" != 'loggy'  \
    -a "$NAME" != 'tweety'  \
    -a "$NAME" != 'tiledrop'  \
    -a "$NAME" != 'alldestination'  \
    -a "$NAME" != 'sitemapy'  \
    -a "$NAME" != 'contentstats'  \
    -a "$NAME" != 'solrybulk'  \
    -a "$NAME" != 'streamy'  \
    -a "$NAME" != 'eventstats'  \
    -a "$NAME" != 'sanitar'  \
    -a "$NAME" != 'solrysingle'  \
    -a "$NAME" != 's3'  \
    -a "$NAME" != 'domestos'  \
    -a "$NAME" != 'activity'  \
    -a "$NAME" != 'eventloggy'  \
    -a "$NAME" != 'imaginator'  \
    -a "$NAME" != 'imageindexer'  \
    -a "$NAME" != 'followrecommended'  \
    -a "$NAME" != 'userindexer'  \
    ]
then
    echo "Usage: "$0" {start|stop|restart} {manager|feedy|loggy|tweety|tiledrop|alldestination|sitemapy|contentstats|solrybulk|streamy|domestos|eventstats|sanitar|solrysingle|activity|eventloggy|imaginator|imageindexer|followrecommended}"
    exit 1;
fi
#    -a "$TYPE" != 'domestos'  \   //not really ready

PIDFILE=/tmp/worker_$NAME.pid
#This is the command to be run, give the full pathname
DAEMON=/root/bin/workers_bin/workerd
DAEMON_OPTS=" $NAME "

export PATH="${PATH:+$PATH:}/usr/sbin:/sbin"
case "$1" in
    start)
    if [ -s $PIDFILE ]
    then
        echo "Error: Pid file $PIDFILE exists.  Will not attempt to start. Check if service still running. ps aux |grep workerd|grep $NAME"
        exit 1;
    fi
    echo -n "Starting daemon: "$NAME
    if start-stop-daemon --start --pidfile $PIDFILE -b --exec $DAEMON -- $DAEMON_OPTS
    then
        rm -f $PIDFILE
    fi
    echo "."
    ;;
    stop)
    echo "Stopping daemon: "$NAME
    if start-stop-daemon --stop --oknodo --pidfile $PIDFILE
    then
        rm -f $PIDFILE
    else
        echo "Error:  Not stop deamon properly.    try   ps aux |grep workerd |grep $NAME to examine"
        exit 1;
    fi
    ;;
    restart)
    echo "Restarting daemon: "$NAME
    if start-stop-daemon --stop --quiet --oknodo --retry 30 --pidfile $PIDFILE
    then
        rm -f $PIDFILE
    else
        echo "Error:  Not stop deamon properly will not attempt to start.    try   ps aux |grep workerd |grep $NAME to examine"
        exit 1;
    fi
    start-stop-daemon --start --quiet --pidfile $PIDFILE -b --exec $DAEMON -- $DAEMON_OPTS
    ;;
    *)
    echo "Usage: "$0" {start|stop|restart} {manager|feedy|loggy|tweety|tiledrop|alldestination|sitemapy|contentstats|solrybulk|streamy|domestos|eventstats|sanitar|solrysingle|activity|eventloggy|imaginator|imageindexer|followrecommended}"
    exit 1
esac

exit 0


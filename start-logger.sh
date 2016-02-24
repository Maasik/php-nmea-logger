#!/bin/sh

cd /home/ora/gps-logger/
rm .gpsstoplog
/usr/bin/php logger.php > /dev/null 2>&1 &

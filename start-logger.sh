#!/bin/sh

cd /home/ora/gps-logger/
/usr/bin/php logger.php > /home/ora/gps-logger/logger.log 2>&1 &

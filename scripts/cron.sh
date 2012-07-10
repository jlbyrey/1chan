#!/bin/sh

cd /var/www/1chan.ru/scripts
pgrep -f 'serverstatus.php' || php5 serverstatus.php&

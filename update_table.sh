#!/bin/sh
set -e
if /usr/bin/php -d display_errors=1 -d error_reporting=2147483647 script/insert.php  && /usr/bin/php -d display_errors=1 -d error_reporting=2147483647 script/diff.php; then
   echo "\e[1m\e[33mUpdate Success !";
else
   echo "\e[1m\e[33mUpdate Failed !";
fi
 

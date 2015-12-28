#!/bin/bash

set -e

/usr/sbin/rsyslogd

/usr/sbin/nginx && /usr/sbin/php-fpm

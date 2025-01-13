#!/usr/bin/env bash
/etc/cron.daily/nginx-cloudflare-ips.sh
service nginx start
php-fpm
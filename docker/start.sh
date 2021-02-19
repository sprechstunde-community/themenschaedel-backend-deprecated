#!/usr/bin/env bash

set -e

WEBROOT=/var/www

php $WEBROOT/artisan config:cache

sleep 2 # Wait for database to fully start
php $WEBROOT/artisan migrate --force

case "$1" in
scheduler)
    exec php $WEBROOT/artisan schedule:work
    ;;
webserver)
    exec apache2-foreground
    ;;
worker)
    exec php $WEBROOT/artisan queue:work
    ;;
*)
    echo "Please define which service to start" >&2
    exit 1
    ;;
esac

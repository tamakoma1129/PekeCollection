#!/bin/sh
if [ "$APP_ROLE" = "octane" ]; then
    if [ ! -d vendor ]; then
        echo "'vendor' が無いため作成します。"
        composer install
        php artisan octane:install --server=swoole
    fi

    if [ ! -f .env ]; then
        echo "'.env' が無いため作成します。"
        cp .env.example .env
        php artisan key:generate
    fi

    if [ ! -f "storage/database.sqlite" ]; then
        echo "'storage/database.sqlite' が無いため作成します。"
        touch storage/database.sqlite
    fi

    /usr/bin/supervisord -c /etc/supervisor/supervisord.conf &
    npm install && npm run dev -- --host 0.0.0.0 &

    php artisan optimize:clear

    exec php artisan octane:start --watch --server=swoole --host=0.0.0.0 --port=80
elif [ "$APP_ROLE" = "queue-worker" ]; then
    /usr/bin/supervisord -c /etc/supervisor/supervisord.conf &

    exec php artisan queue:work --sleep=3 --timeout=300
fi

#!/bin/sh

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
    php artisan migrate --force
fi

npm install && npm run dev -- --host 0.0.0.0 &

php artisan optimize:clear

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf

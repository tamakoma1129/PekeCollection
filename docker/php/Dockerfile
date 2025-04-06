#####################################
# Stage 0: 共通ベース
#####################################
FROM php:8.2-cli AS base

# git : Laravelをgit pullする為
# unzip : Composerに必要。解凍するもの。
# libzip-dev : zipに必要。
# libsqlite3-dev : SQLiteに必要
# libonig-dev : mbstringに必要。
# zlib1g-dev : libpngに必要
# libpng-dev : GDに必要
# libjpeg62-turbo-dev : GDに必要。高速版。
# libwebp-dev : GDに必要
# ffmpeg : laravel-ffmpegに必要
# procps : Laravel Pulseで必要
# supervisor : pulse:checkを常時稼働するのに必要
RUN apt-get update  \
    && apt-get -y install --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libsqlite3-dev \
        libonig-dev \
        zlib1g-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        ffmpeg \
        procps \
        supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# openswoole : octaneに必要
# gd : interventionに必要。
# zip : Laravelのプロジェクト作成に必要。
# pdo_sqlite : sqliteに必要
# mbstring : intervetionに必要。
# exif : intervetionに必要。
# pcntl : Octaneに必要
RUN pecl install openswoole \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install zip pdo_sqlite mbstring exif gd pcntl \
    && docker-php-ext-enable openswoole

#####################################
# Stage 1: PHP アプリケーションのビルド
#####################################
FROM base AS php-builder

# Laravelのコピー
COPY .. /PekeCollection/
WORKDIR /PekeCollection/

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 依存関係のインストール & Octane セットアップ
RUN composer install --optimize-autoloader --no-dev \
    && composer clear-cache \
    && php artisan octane:install --server=swoole

# 初期設定
COPY ./.env.example ./.env
RUN php artisan key:generate && \
    php artisan reverb:install

#####################################
# Stage 2: フロントエンドのビルド
#####################################
FROM node:lts-alpine AS node-builder
WORKDIR /PekeCollection

COPY --from=php-builder /PekeCollection /PekeCollection
RUN npm install && npm run build

#####################################
# Stage 3: 最終ビルド
#####################################
FROM base

# Supervisorで使う設定ファイルをコピー
COPY ./docker/supervisor/pulse.conf /etc/supervisor/conf.d/pulse.conf

# php-builder で構築したアプリケーションと node-builder の成果物をコピー
COPY --from=node-builder /PekeCollection /PekeCollection
WORKDIR /PekeCollection/

# エントリポイントスクリプトのコピー＆権限付与
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]

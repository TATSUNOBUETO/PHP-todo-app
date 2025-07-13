FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# MySQL（PDO）を使用しているので、必要な拡張機能をインストール
RUN docker-php-ext-install pdo pdo_mysql mbstring mysqli \
    zip \
    opcache

RUN a2enmod rewrite
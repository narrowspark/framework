FROM php:7.2.0-fpm

RUN apt-get update && \
    apt install -y apt-utils libsqlite3-dev libsqlite3-0 libxml2-dev  && \
    pecl install xdebug-2.6.0alpha1 && \
    pecl install redis && \
    docker-php-ext-enable xdebug mbstring redis pdo pdo_sqlite pdo_mysql redis curl fileinfo opcache xml && \
    php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
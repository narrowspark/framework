FROM php:7.2.0

RUN apt-get update && \
    apt-get install -y \
        apt-utils \
        libsqlite3-dev \
        libsqlite3-0 \
        libxml2-dev \
        curl \
        libsodium-dev \
        zlib1g-dev \
        libicu-dev \
        git \
        g++ \
        unzip \
        libtool \
        make \
        build-essential \
        automake \
        ca-certificates && \
    apt-get autoremove && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN pecl install -f libsodium && \
    pecl install xdebug-2.6.0 && \
    pecl install redis

RUN docker-php-ext-configure intl

RUN docker-php-ext-install pdo_mysql opcache sodium intl bcmath zip

RUN docker-php-ext-enable redis

RUN { \
        echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)"; \
        echo 'xdebug.idekey=PHPSTORM'; \
        echo 'xdebug.remote_port=9000'; \
        echo 'xdebug.remote_enable=on'; \
        echo 'xdebug.remote_connect_back=on'; \
        echo 'xdebug.profiler_output_dir="/var/log/xdebug"'; \
    } > /usr/local/etc/php/conf.d/xdebug.ini && \
    { \
        echo "short_open_tag=off"; \
        echo "date.timezone=Europe/Berlin"; \
        echo "opcache.max_accelerated_files=20000"; \
        echo "realpath_cache_size=4096K"; \
        echo "realpath_cache_ttl=600"; \
    } > /usr/local/etc/php/php.ini

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer && \
    composer global require localheinz/composer-normalize && \
    export PATH=/root/.composer/vendor/bin:$PATH

WORKDIR /var/www/framework

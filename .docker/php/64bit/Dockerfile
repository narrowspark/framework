ARG PHP_VERSION

FROM php:${PHP_VERSION}-cli

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
        libzip-dev \
        zip \
        libtool \
        make \
        build-essential \
        automake \
        ca-certificates && \
    apt-get autoremove -y && \
    apt-get clean -y && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ARG INSTALL_XDEBUG
ARG XDEBUG_VERSION

RUN pecl install -f libsodium && \
    pecl install redis && \
    pecl install pcov && \
    pecl install inotify

RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    pecl install "xdebug-${XDEBUG_VERSION}" \
    echo 'xdebug.idekey=PHPSTORM \
xdebug.remote_port=9000 \
xdebug.remote_enable=on \
xdebug.remote_connect_back=on \
xdebug.profiler_output_dir="/var/log/xdebug" \
xdebug.cli_color=1 \
xdebug.remote_enable=1 \
xdebug.remote_autostart=1';  > /usr/local/etc/php/conf.d/xdebug.ini \
    docker-php-ext-enable xdebug \
;fi

RUN docker-php-ext-configure intl && \
    docker-php-ext-configure zip

RUN docker-php-ext-install pdo_mysql opcache sodium intl bcmath zip pcntl

RUN docker-php-ext-enable redis inotify

RUN { \
        echo "short_open_tag=off"; \
        echo "date.timezone=Europe/Berlin"; \
        echo "opcache.max_accelerated_files=20000"; \
        echo "realpath_cache_size=4096K"; \
        echo "realpath_cache_ttl=600"; \
        echo "error_reporting = E_ALL"; \
        echo "display_startup_errors = Off"; \
        echo "ignore_repeated_errors = Off"; \
        echo "ignore_repeated_source = Off"; \
        echo "html_errors = Off"; \
        echo "track_errors = Off"; \
        echo "display_errors = Off"; \
        echo "log_errors = On"; \
        echo "error_log = /var/log/php/cli-error.log"; \
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

ENV COMPOSER_ALLOW_SUPERUSER 1
ARG INSTALL_CODING_STANDARD="false"

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer && \
    composer global require phploc/phploc && \
    composer global require localheinz/composer-normalize

RUN mkdir /var/log/php && touch /var/log/php/cli-error.log && chmod 0664 /var/log/php/cli-error.log

WORKDIR /var/www/framework

ENV PATH="/root/.composer/vendor/bin:${PATH}"

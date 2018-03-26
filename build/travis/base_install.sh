#!/usr/bin/env bash

export PATH="$HOME/.composer/vendor/bin:$HOME/.config/composer/vendor/bin:$PATH"
composer global require hirak/prestissimo
composer require roave/security-advisories:dev-master

$COMPOSER_UP

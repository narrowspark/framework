<?php
declare(strict_types=1);

/*
 |--------------------------------------------------------------------------
 | Implements rewrite rules for PHP built-in web server
 |--------------------------------------------------------------------------
 |
 | This provides a convenient way to test a application
 | without having installed a "real" web server software here.
 |
 | See: http://www.php.net/manual/en/features.commandline.webserver.php
 |
 */

if (\is_file($_SERVER['DOCUMENT_ROOT'] . \DIRECTORY_SEPARATOR . $_SERVER['SCRIPT_NAME'])) {
    return false;
}

$script = \getenv('APP_FRONT_CONTROLLER') ?? 'index.php';

$_SERVER                    = \array_merge($_SERVER, $_ENV);
$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . \DIRECTORY_SEPARATOR . $script;

// Adjust SCRIPT_NAME and PHP_SELF accordingly
$_SERVER['SCRIPT_NAME'] = \DIRECTORY_SEPARATOR . $script;
$_SERVER['PHP_SELF']    = \DIRECTORY_SEPARATOR . $script;

require $script;

\error_log(\sprintf('%s:%d [%d]: %s', $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'], \http_response_code(), $_SERVER['REQUEST_URI']), 4);

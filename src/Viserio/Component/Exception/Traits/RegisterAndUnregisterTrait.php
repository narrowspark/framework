<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Exception\Traits;

use Symfony\Component\Debug\DebugClassLoader;
use Viserio\Component\Exception\Console\Handler as ConsoleHandler;
use Viserio\Component\Exception\ErrorHandler;
use Viserio\Component\Exception\ExceptionIdentifier;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Http\Handler as HttpHandler;

trait RegisterAndUnregisterTrait
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        \error_reporting(\E_ALL);

        // Ensures we don't hit https://bugs.php.net/42098
        \class_exists(HttpHandler::class);
        \class_exists(ConsoleHandler::class);
        \class_exists(ErrorHandler::class);
        \class_exists(ExceptionIdentifier::class);
        \class_exists(ExceptionInfo::class);

        // The DebugClassLoader attempts to throw more helpful exceptions
        // when a class isn't found by the registered autoloaders.
        DebugClassLoader::enable();

        $this->registerErrorHandler();

        $this->registerExceptionHandler();

        if ($this->resolvedOptions['env'] !== 'testing') {
            $this->registerShutdownHandler();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unregister(): void
    {
        \restore_error_handler();
    }

    /**
     * Register the PHP error handler.
     */
    abstract protected function registerErrorHandler(): void;

    /**
     * Register the PHP exception handler.
     */
    abstract protected function registerExceptionHandler(): void;

    /**
     * Register the PHP shutdown handler.
     */
    abstract protected function registerShutdownHandler(): void;
}

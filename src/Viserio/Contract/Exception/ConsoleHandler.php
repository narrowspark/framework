<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Exception;

use Throwable;

interface ConsoleHandler extends Handler
{
    /**
     * Register the exception / Error handlers for the application.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Unregister the PHP error handler.
     *
     * @return void
     */
    public function unregister(): void;

    /**
     * Render an exception to the console.
     *
     * @param \Viserio\Contract\Exception\ConsoleOutput $output
     * @param Throwable                                 $exception
     *
     * @return void
     */
    public function render(ConsoleOutput $output, Throwable $exception): void;
}

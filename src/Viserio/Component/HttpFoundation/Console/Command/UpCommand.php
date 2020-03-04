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

namespace Viserio\Component\HttpFoundation\Console\Command;

use Throwable;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;

class UpCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'app:up';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Bring the application out of maintenance mode';

    /**
     * {@inheritdoc}
     */
    public function handle(ConsoleKernelContract $kernel): int
    {
        try {
            $downFilePath = $kernel->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'down');

            if (! file_exists($downFilePath)) {
                $this->comment('Application is already up.');

                return 0;
            }

            \unlink($downFilePath);
        } catch (Throwable $exception) {
            $this->error('Application is failed to up.');
            $this->error($exception->getMessage());

            return 1;
        }

        $this->info('Application is now live.');

        return 0;
    }
}

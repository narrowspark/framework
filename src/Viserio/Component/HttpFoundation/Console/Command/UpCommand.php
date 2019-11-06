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

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

use Cake\Chronos\Chronos;
use Throwable;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;

class DownCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'app:down';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'app:down
        [--message= : The message for the maintenance mode.]
        [--retry= : The number of seconds after which the request may be retried.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Put the application into maintenance mode';

    /**
     * {@inheritdoc}
     */
    public function handle(ConsoleKernelContract $kernel): int
    {
        try {
            \file_put_contents(
                $kernel->getStoragePath('framework' . DIRECTORY_SEPARATOR . 'down'),
                \json_encode($this->getDownPayload(), JSON_PRETTY_PRINT)
            );
        } catch (Throwable $exception) {
            $this->error('Application is failed to enter maintenance mode.');
            $this->error($exception->getMessage());

            return 1;
        }

        $this->comment('Application is now in maintenance mode.');

        return 0;
    }

    /**
     * Get the information that are put into the "down" file.
     *
     * @return array
     */
    protected function getDownPayload(): array
    {
        return [
            'time' => Chronos::now()->getTimestamp(),
            'message' => $this->option('message'),
            'retry' => $this->getRetryTime(),
        ];
    }

    /**
     * Get the number of seconds the client should wait before retrying their request.
     *
     * @return null|int
     */
    protected function getRetryTime(): ?int
    {
        $retry = $this->option('retry');

        return \is_numeric($retry) && $retry > 0 ? (int) $retry : null;
    }
}

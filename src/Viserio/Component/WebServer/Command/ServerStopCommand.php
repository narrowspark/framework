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

namespace Viserio\Component\WebServer\Command;

use Exception;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\WebServer\WebServer;

final class ServerStopCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'server:stop';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'server:stop
        [--pidfile= : PID file.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Stops the local web server that was started with the server:start command.';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        try {
            WebServer::stop($this->option('pidfile'));

            $this->getOutput()->success('Stopped the web server.');

            return 0;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return 1;
        }
    }
}

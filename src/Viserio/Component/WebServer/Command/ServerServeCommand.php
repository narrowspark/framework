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

namespace Viserio\Component\WebServer\Command;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\WebServer\Command\Traits\ServerCommandRequirementsCheckTrait;
use Viserio\Component\WebServer\WebServer;
use Viserio\Component\WebServer\WebServerConfig;

final class ServerServeCommand extends AbstractCommand
{
    use ServerCommandRequirementsCheckTrait;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'server:serve';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'server:serve
        [-H|--host= : The hostname to listen to.]
        [-p|--port= : The port to listen to.]
        [-r|--router= : Path to custom router script.]
        [--pidfile= : PID file.]
        [--disable-xdebug : Disable xdebug on server]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Runs a local web server.';

    /**
     * Create a new ServerServeCommand Instance.
     *
     * @param string $documentRoot
     * @param string $environment
     */
    public function __construct(string $documentRoot, string $environment)
    {
        $this->documentRoot = $documentRoot;
        $this->environment = $environment;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        if ($this->checkRequirements() === 1) {
            return 1;
        }

        $webServerConfig = new WebServerConfig($this->documentRoot, $this->environment, $this);

        $callback = null;
        $disableOutput = false;
        $output = $this->getOutput();

        if ($output->isQuiet()) {
            $disableOutput = true;
        } else {
            $callback = static function ($type, $buffer) use ($output): void {
                if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }

                $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
            };
        }

        try {
            $host = $webServerConfig->getHostname();
            $port = $webServerConfig->getPort();
            $resolvedAddress = $webServerConfig->getDisplayAddress();

            $output->success(\sprintf(
                'Server listening on %s%s',
                $host !== '0.0.0.0' ? $host . ':' . $port : 'all interfaces, port ' . $port,
                $resolvedAddress !== null ? \sprintf(' -- see http://%s)', $resolvedAddress) : ''
            ));

            if ($webServerConfig->hasXdebug()) {
                $output->comment('Xdebug profiler trigger enabled.');
            }

            WebServer::run($webServerConfig, $disableOutput, $callback);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        return 0;
    }
}

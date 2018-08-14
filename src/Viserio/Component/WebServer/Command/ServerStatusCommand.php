<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException;
use Viserio\Component\WebServer\WebServer;

final class ServerStatusCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'server:status';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'server:status
        [--pidfile= : PID file.]
        [--filter= : The value to display (one of port, host, or address).]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Outputs the status of the local web server for the given address.';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        if ($filter = $this->option('filter')) {
            if (WebServer::isRunning($this->option('pidfile'))) {
                [$host, $port] = \explode(':', $address = WebServer::getAddress($this->option('pidfile')));

                if ($filter === 'address') {
                    $this->line($address);
                } elseif ($filter === 'host') {
                    $this->line($host);
                } elseif ($filter === 'port') {
                    $this->line($port);
                } else {
                    throw new InvalidArgumentException(\sprintf('[%s] is not a valid filter.', $filter));
                }

                return 0;
            }
        }

        if (WebServer::isRunning($this->option('pidfile'))) {
            $this->getOutput()->success(\sprintf('Web server still listening on http://%s', WebServer::getAddress($this->option('pidfile'))));

            return 0;
        }

        $this->warn('No web server is listening.');

        return 1;
    }
}

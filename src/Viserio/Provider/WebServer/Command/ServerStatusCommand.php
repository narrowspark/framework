<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Component\Console\Command\Command;
use Viserio\Provider\WebServer\WebServer;

class ServerStatusCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'server:status';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Outputs the status of the local web server for the given address.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $server  = new WebServer();
        $pidFile = $this->option('pidfile');

        if ($filter = $this->option('filter')) {
            if ($server->isRunning($pidFile)) {
                list($host, $port) = explode(':', $address = $server->getAddress($pidFile));

                if ($filter === 'address') {
                    $this->info($address);
                } elseif ($filter === 'host') {
                    $this->info($host);
                } elseif ($filter === 'port') {
                    $this->info($port);
                } else {
                    throw new InvalidArgumentException(sprintf('"%s" is not a valid filter.', $filter));
                }
            } else {
                return 1;
            }
        } else {
            if ($server->isRunning($pidFile)) {
                $this->getOutput()->success(sprintf('Web server still listening on http://%s', $server->getAddress($pidFile)));
            } else {
                $this->warn('No web server is listening.');

                return 1;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            ['pidfile', null, InputOption::VALUE_REQUIRED, 'Path to the pidfile.'],
            ['filter', null, InputOption::VALUE_REQUIRED, 'The value to display (one of port, host, or address.'],
        ];
    }
}

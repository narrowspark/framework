<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Command;

use Symfony\Component\Console\Input\InputOption;
use Throwable;
use Viserio\Provider\WebServer\WebServer;
use Viserio\Component\Console\Command\Command;

class ServerStopCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'server:stop';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Stops the local web server that was started with the server:start command.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        try {
            $server = new WebServer();
            $server->stop($this->option('pidfile'));

            $this->success('Stopped the web server.');
        } catch (Throwable $exception) {
            $this->getOutput()->error($exception->getMessage());

            return 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            ['pidfile', null, InputOption::VALUE_REQUIRED, 'Path to the pidfile.'],
        ];
    }
}

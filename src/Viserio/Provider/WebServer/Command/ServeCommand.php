<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Viserio\Component\Console\Command\Command;
use Viserio\Provider\WebServer\WebServer;

class ServeCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'serve';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Runs a local web server.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        if (($documentRoot = $this->option('docroot')) === null) {
            $this->error('The document root directory must be either passed as first argument of the constructor or through the "--docroot" input option.');

            return 1;
        }

        if (! is_dir($documentRoot)) {
            $this->error(sprintf('The document root directory "%s" does not exist.', $documentRoot));

            return 1;
        }

        $callback = null;
        $output   = $this->getOutput();

        try {
            $server = new WebServer();
            $server = $this->configureWebServer($server);

            if ($server->isRunning($pidfile = $this->option('pidfile'))) {
                $this->error(sprintf('The web server is already running (listening on http://%s).', $server->getAddress($pidfile)));

                return 1;
            }

            $output->success(sprintf('Server listening on http://%s:%s', $server->getHostname(), $server->getPort()));
            $this->comment('Quit the server with CONTROL-C.');

            $server->run($documentRoot, $output->isQuiet(), $this->getErrorCallback($output->isQuiet()));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            ['pidfile', null, InputOption::VALUE_REQUIRED, 'Path to the pidfile.'],
            ['host', 'H', InputOption::VALUE_REQUIRED, 'The hostname to listen to.'],
            ['port', 'p', InputOption::VALUE_REQUIRED, 'The port to listen to.'],
            ['docroot', 'd', InputOption::VALUE_REQUIRED, 'Path to the document root.'],
            ['router', 'r', InputOption::VALUE_REQUIRED, 'Path to custom router script.'],
            ['background', null, InputOption::VALUE_NONE, 'Starts the server as a background process.'],
        ];
    }

    /**
     * Configure the webserver with hostname, port and router path.
     * If option and argument are not empty.
     *
     * @param \Viserio\Provider\WebServer\WebServer $server
     *
     * @return \Viserio\Provider\WebServer\WebServer
     */
    private function configureWebServer(WebServer $server): WebServer
    {
        if (($addressport = $this->argument('addressport')) !== null) {
            list($host, $port) = explode(':', $addressport);

            if ($host !== null && ! ctype_digit($host)) {
                $server->setHostname($host);
            }

            if ($port !== null || ctype_digit($host)) {
                $server->setPort((int) ($port ?? host));
            }
        }

        if ($router = $this->option('router') !== null) {
            $server->setRouter($router);
        }

        return $server;
    }

    /**
     * Returns the error callback if console is not quiet.
     *
     * @param bool $quiet
     *
     * @return callback|null
     */
    private function getErrorCallback(bool $quiet)
    {
        if ($quiet === true) {
            return null;
        }

        $output = $this->getOutput();

        return function ($type, $buffer) use ($output) {
            if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }

            $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
        };
    }
}

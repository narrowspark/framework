<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Command;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\WebServer\Command\Traits\ServerCommandRequirementsCheckTrait;
use Viserio\Component\WebServer\WebServer;

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
     * @param null|string $documentRoot
     * @param null|string $environment
     */
    public function __construct(?string $documentRoot = null, ?string $environment = null)
    {
        $this->documentRoot = $documentRoot;
        $this->environment  = $environment;

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

        $callback      = null;
        $disableOutput = false;
        $output        = $this->getOutput();

        if ($output->isQuiet()) {
            $disableOutput = true;
        } else {
            $callback = function ($type, $buffer) use ($output) {
                if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }

                $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
            };
        }

        try {
            $config = $this->prepareConfig();

            [$host, $port]   = \explode(':', WebServer::getAddress($config['pidfile']), 2);
            $resolvedAddress = WebServer::getDisplayAddress($host, $port);

            $output->success(\sprintf(
                'Server listening on %s%s',
                $host !== '0.0.0.0' ? $host . ':' . $port : 'all interfaces, port' . $port,
                $resolvedAddress !== null ? \sprintf(' -- see http://%s)', $resolvedAddress) : ''
            ));

            if ($config['disable-xdebug'] === false) {
                $output->comment('Xdebug profiler trigger enabled.');
            }

            WebServer::run($config, $disableOutput, $callback);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Prepare the config for the web server.
     *
     * @return array
     */
    private function prepareConfig(): array
    {
        $config = [
            'document_root'  => $this->documentRoot,
            'env'            => $this->environment,
            'disable-xdebug' => ! \ini_get('xdebug.profiler_enable_trigger'),
        ];

        if ($this->hasOption('host')) {
            $config['host'] = $this->option('host');
        }

        if ($this->hasOption('port')) {
            $config['port'] = $this->option('port');
        }

        if ($this->hasOption('router')) {
            $config['router'] = $this->option('router');
        }

        if ($this->hasOption('pidfile')) {
            $config['pidfile'] = $this->option('pidfile');
        }

        if ($this->hasOption('disable-xdebug')) {
            $config['disable-xdebug'] = true;
        }

        return $config;
    }
}

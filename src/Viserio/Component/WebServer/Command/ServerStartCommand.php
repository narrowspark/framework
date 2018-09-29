<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Events\EventManager;
use Viserio\Component\WebServer\Command\Traits\ServerCommandRequirementsCheckTrait;
use Viserio\Component\WebServer\WebServer;

final class ServerStartCommand extends AbstractCommand
{
    use ServerCommandRequirementsCheckTrait;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'server:start';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'server:start
        [-H|--host= : The hostname to listen to.]
        [-p|--port= : The port to listen to.]
        [-r|--router= : Path to custom router script.]
        [--pidfile= : PID file.]
        [--disable-xdebug : Disable xdebug on server]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Starts a local web server in the background.';

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
        if (! \extension_loaded('pcntl')) {
            $this->error('This command needs the pcntl extension to run. You can either install it or use the "server:serve" command instead.');

            if ($this->confirm('Do you want to execute <info>server:serve</info> immediately?', false)) {
                return $this->call('server:serve');
            }

            return 1;
        }

        if ($this->checkRequirements() === 1) {
            return 1;
        }

        // replace event dispatcher with an empty one to prevent console.terminate from firing
        // as container could have changed between start and stop
        if (\class_exists(EventManager::class)) {
            /** @param \Viserio\Component\Console\Application $application */
            $application = $this->getApplication();
            $application->setEventManager(new EventManager());
        }

        try {
            $config = $this->prepareConfig();

            if (WebServer::isRunning($config['pidfile'])) {
                $this->error(\sprintf(
                    'The web server has already been started. It is currently listening on http://%s. Please stop the web server before you try to start it again.',
                    WebServer::getAddress($config['pidfile'])
                ));

                return 1;
            }

            if (WebServer::STARTED === WebServer::start($config, $config['pidfile'])) {
                $resolvedAddress = WebServer::getResolvedAddress($config['host'], $config['port']);

                $this->getOutput()->success(\sprintf(
                    'Server listening on http://%s%s%s',
                    WebServer::getAddress($config['pidfile']),
                    $resolvedAddress !== null ? \sprintf(' (resolved as http://%s)', $resolvedAddress) : '',
                    $config['disable-xdebug'] === false ? ' with Xdebug profiler trigger enabled.' : ''
                ));
            }

            return 0;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return 1;
        }
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

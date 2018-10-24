<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Events\EventManager;
use Viserio\Component\WebServer\Command\Traits\ServerCommandRequirementsCheckTrait;
use Viserio\Component\WebServer\WebServer;
use Viserio\Component\WebServer\WebServerConfig;

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
     * Create a new ServerStartCommand Instance.
     *
     * @param string $documentRoot
     * @param string $environment
     */
    public function __construct(string $documentRoot, string $environment)
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
            $webServerConfig = new WebServerConfig($this->documentRoot, $this->environment, $this);

            $pidFile = $webServerConfig->getPidFile();

            if (WebServer::isRunning($pidFile)) {
                $this->error(\sprintf(
                    'The web server has already been started. It is currently listening on http://%s. Please stop the web server before you try to start it again.',
                    WebServer::getAddress($pidFile)
                ));

                return 1;
            }

            if (WebServer::STARTED === WebServer::start($webServerConfig, $pidFile)) {
                $output          = $this->getOutput();
                $host            = $webServerConfig->getHostname();
                $port            = $webServerConfig->getPort();
                $resolvedAddress = $webServerConfig->getDisplayAddress();

                $output->success(\sprintf(
                    'Server listening on %s%s',
                    $host !== '0.0.0.0' ? $host . ':' . $port : 'all interfaces, port ' . $port,
                    $resolvedAddress !== null ? \sprintf(' -- see http://%s)', $resolvedAddress) : ''
                ));

                if ($webServerConfig->hasXdebug()) {
                    $output->comment('Xdebug profiler trigger enabled.');
                }
            }

            return 0;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return 1;
        }
    }
}

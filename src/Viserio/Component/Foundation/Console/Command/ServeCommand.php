<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class ServeCommand extends Command
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'serve';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Runs a local web server.';

    /**
     * Web server hostname.
     *
     * @var string
     */
    private $hostname;

    /**
     * Web server port.
     *
     * @var int
     */
    private $port;

    /**
     * {@inheritdoc}
     */
    public function handle(ConsoleKernelContract $kernel)
    {
        $documentRoot = $kernel->getPublicPath();

        if (! \is_dir($documentRoot)) {
            $this->error(\sprintf('The document root directory [%s] does not exist.', $documentRoot));

            return 1;
        }

        $controller = $this->option('controller');
        $file       = self::normalizeDirectorySeparator($documentRoot . '/' . $controller);

        if (! \file_exists($file)) {
            $this->error(\sprintf('Unable to find the controller under [%s] (file not found: %s).', $documentRoot, $controller));

            return 1;
        }

        \putenv('APP_WEBSERVER_CONTROLLER=' . $file);

        $this->configureCommand();

        $callback = null;
        $output   = $this->getOutput();

        try {
            if (\file_exists($pidFilePath = $this->getDefaultPidFile())) {
                $this->error(\sprintf(
                    'The web server is already running (listening on http://%s).',
                    \file_get_contents($pidFilePath)
                ));

                return 1;
            }

            $output->success(\sprintf('Server listening on http://%s:%s', $this->hostname, $this->port));
            $this->comment('Quit the server with CONTROL-C.');

            $this->runServer($documentRoot, $output->isQuiet(), $this->getErrorCallback($output->isQuiet()));
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
            ['host', 'H', InputOption::VALUE_REQUIRED, 'The hostname to listen to.', '127.0.0.1'],
            ['port', 'p', InputOption::VALUE_REQUIRED, 'The port to listen to.', 8000],
            ['controller', null, InputOption::VALUE_REQUIRED, 'File name of controller.', 'index.php'],
        ];
    }

    /**
     * Runs the php build-in web server.
     *
     * @param string        $documentRoot
     * @param bool          $disableOutput
     * @param null|callable $callback
     *
     * @throws \RuntimeException Server terminated unexpected
     *
     * @return void
     */
    private function runServer(
        string $documentRoot,
        bool $disableOutput = true,
        callable $callback = null
    ): void {
        $process = $this->createServerProcess($documentRoot);

        if ($disableOutput) {
            $process->disableOutput();
            $callback = null;
        } else {
            try {
                $process->setTty(true);
                $callback = null;
            } catch (RuntimeException $e) {
            }
        }

        $process->run($callback);

        if (! $process->isSuccessful()) {
            $error = 'Server terminated unexpectedly.';

            if ($process->isOutputDisabled()) {
                $error .= ' Run the command again with -v option for more details.';
            }

            throw new RuntimeException($error);
        }
    }

    /**
     * Get the default pid file path.
     *
     * @return string
     */
    private function getDefaultPidFile(): string
    {
        return \getcwd() . '/.web-server-pid';
    }

    /**
     * Configure the command with hostname and port.
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private function configureCommand(): void
    {
        $this->hostname = $this->option('host');
        $port           = (int) $this->option('port');

        if (! \ctype_digit($port)) {
            throw new InvalidArgumentException(\sprintf('Port "%s" is not valid.', $port));
        }

        $this->port = $port;
    }

    /**
     * Returns the error callback if console is not quiet.
     *
     * @param bool $quiet
     *
     * @return null|callable
     */
    private function getErrorCallback(bool $quiet)
    {
        if ($quiet === true) {
            return null;
        }

        $output = $this->getOutput();

        return function ($type, $buffer) use ($output): void {
            if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }

            $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
        };
    }

    /**
     * Create a new Server process.
     *
     * @param string $documentRoot
     *
     * @throws \RuntimeException If no php binary was found
     *
     * @return \Symfony\Component\Process\Process
     */
    private function createServerProcess(string $documentRoot): Process
    {
        $finder = new PhpExecutableFinder();

        if (($binary = $finder->find(true)) === false) {
            throw new RuntimeException('Unable to find the PHP binary.');
        }

        $process = new Process([
            $binary,
            '-S',
            \sprintf('%s:%s', $this->hostname, $this->port),
            __DIR__ . '/../Resources/router.php',
        ]);
        $process->setWorkingDirectory($documentRoot);
        $process->setTimeout(null);

        return $process;
    }
}

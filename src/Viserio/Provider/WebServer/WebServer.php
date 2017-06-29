<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer;

use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class WebServer implements
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * @var int
     */
    private const STARTED = 0;

    /**
     * @var int
     */
    private const STOPPED = 1;

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'webserver'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'hostname'   => '127.0.0.1',
            'port'       => null,
            'controller' => 'index.php',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'document_root',
        ];
    }

    /**
     * Undocumented function.
     *
     * @param mixed  $config
     * @param string $pidFilePath
     *
     * @return int
     */
    public function start($config, string $pidFilePath = null): int
    {
        $options = $this->resolveOptions($config);
        $port    = $options['port'] ?? self::findBestPort();

        if (! ctype_digit($port)) {
            throw new InvalidArgumentException(sprintf('Port "%s" is not valid.', $port));
        }

        $pidFilePath = $this->getPidFilePath($pidFilePath);
        $address     = sprintf('%s:%s', $options['hostname'], $port);

        if ($this->isRunning($pidFilePath)) {
            throw new RuntimeException(sprintf('A process is already listening on http://%s.', $address));
        }

        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new RuntimeException('Unable to start the server process.');
        }

        if ($pid > 0) {
            return self::STARTED;
        }

        if (posix_setsid() < 0) {
            throw new RuntimeException('Unable to set the child process as session leader.');
        }

        $process = $this->createServerProcess($config);
        $process->disableOutput();
        $process->start();

        if (! $process->isRunning()) {
            throw new RuntimeException('Unable to start the server process.');
        }

        file_put_contents($pidFile, $address);

        // stop the web server when the lock file is removed
        while ($process->isRunning()) {
            if (! file_exists($pidFile)) {
                $process->stop();
            }

            sleep(1);
        }

        return self::STOPPED;
    }

    /**
     * Stop a running php build-in web server.
     *
     * @param string|null $pidFilePath
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function stop(string $pidFilePath = null): void
    {
        $pidFilePath = $this->getPidFilePath($pidFilePath);

        if (! file_exists($pidFilePath)) {
            throw new RuntimeException('No web server is listening.');
        }

        unlink($pidFilePath);
    }

    /**
     * Undocumented function.
     *
     * @param mixed    $config
     * @param bool     $disableOutput
     * @param callable $callback
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException If port is invalid
     *
     * @return void
     */
    public function run($config, bool $disableOutput = true, callable $callback = null): void
    {
        $options = $this->resolveOptions($config);
        $port    = $options['port'] ?? self::findBestPort();

        if (! ctype_digit($port)) {
            throw new InvalidArgumentException(sprintf('Port "%s" is not valid.', $port));
        }

        if ($this->isRunning()) {
            throw new RuntimeException(sprintf('A process is already listening on http://%s:%s.', $options['hostname'], $port));
        }

        $process = $this->createServerProcess($config);

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
     * Check if a php build-in web server is running.
     *
     * @param string|null $pidFilePath
     *
     * @return bool
     */
    public function isRunning(string $pidFilePath = null): bool
    {
        $pidFilePath = $this->getPidFilePath($pidFilePath);

        if (! file_exists($pidFilePath)) {
            return false;
        }

        $address  = file_get_contents($pidFilePath);
        $pos      = mb_strrpos($address, ':');
        $hostname = mb_substr($address, 0, $pos);
        $port     = mb_substr($address, $pos + 1);

        if ($fp = @fsockopen($hostname, $port, $errno, $errstr, 1) !== false) {
            fclose($fp);

            return true;
        }

        unlink($pidFilePath);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }

    /**
     * Create a new Server process.
     *
     * @return \Symfony\Component\Process\Process The process
     */
    private function createServerProcess(array $config): Process
    {
        $finder = new PhpExecutableFinder();

        if (($binary = $finder->find(true)) === false) {
            throw new RuntimeException('Unable to find the PHP binary.');
        }

        $process = new Process([
            $binary,
            '-S',
            sprintf('%s:%s', $config['host'], $config['hostname']),
            $config['router_path'],
        ]);
        $process->setWorkingDirectory($config->getDocumentRoot());
        $process->setTimeout(null);

        return $process;
    }

    /**
     * Get the default pid file path.
     *
     * @return string
     */
    private function getDefaultPidFile(): string
    {
        return getcwd() . '/.web-server-pid';
    }

    /**
     * Undocumented function.
     *
     * @param string|null $pidFilePath
     *
     * @return string
     */
    private function getPidFilePath(?string $pidFilePath = null): string
    {
        if ($pidFilePath !== null) {
            return $pidFilePath;
        }

        return $this->getDefaultPidFile();
    }

    /**
     * Finds the best port from 8000 to 8100.
     *
     * @return int
     */
    private static function findBestPort(): int
    {
        $port = 8000;

        while ($fp = @fsockopen($this->hostname, $port, $errno, $errstr, 1) !== false) {
            fclose($fp);

            if ($port++ >= 8100) {
                throw new RuntimeException('Unable to find a port available to run the web server.');
            }
        }

        return $port;
    }
}

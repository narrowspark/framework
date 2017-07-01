<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class WebServer
{
    /**
     * @var int
     */
    private const STARTED = 0;

    /**
     * @var int
     */
    private const STOPPED = 1;

    /**
     * WebServer hostname.
     *
     * @var string
     */
    private $hostname = '127.0.0.1';

    /**
     * WebServer port.
     *
     * @var int|null
     */
    private $port;

    /**
     * Path to the router file.
     *
     * @var string
     */
    private $router = __DIR__ . '/Resources/router.php';

    /**
     * File name of controller that should be used.
     *
     * @var string
     */
    private $controller = 'index.php';

    /**
     * Set the web-server hostname.
     *
     * @param string $hostname
     *
     * @return self
     */
    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get the actual web server hostname.
     *
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Set the web server port.
     *
     * @param int $port
     *
     * @return self
     */
    public function setPort(int $port): self
    {
        if (! ctype_digit($port)) {
            throw new InvalidArgumentException(sprintf('Port "%s" is not valid.', $this->port));
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Get the actual web server port.
     *
     * @throws \InvalidArgumentException If port is invalid
     *
     * @return int
     */
    public function getPort(): int
    {
        if ($this->port !== null) {
            return $this->port;
        }

        return self::findBestPort($this->getHostname());
    }

    /**
     * Set the path to your custom router file.
     *
     * @param string $router
     *
     * @return self
     */
    public function setRouter(string $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Get the router file path.
     *
     * @return string
     */
    public function getRouter(): string
    {
        return $this->router;
    }

    /**
     * Set the controller file name.
     *
     * @param string $controller
     *
     * @return self
     */
    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Returns the controller file path.
     *
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Starts the build in php web server.
     *
     * @param string $documentRoot
     * @param string $pidFilePath
     *
     * @return int
     */
    public function start(string $documentRoot, ?string $pidFilePath = null): int
    {
        $pidFilePath = $this->getPidFilePath($pidFilePath);
        $address     = sprintf('%s:%s', $this->getHostname(), $this->getPort());

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

        $process = $this->createServerProcess($documentRoot);
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
    public function stop(?string $pidFilePath = null): void
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
     * @param string   $documentRoot
     * @param bool     $disableOutput
     * @param callable $callback
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException If port is invalid
     *
     * @return void
     */
    public function run(string $documentRoot, bool $disableOutput = true, callable $callback = null): void
    {
        $address = sprintf('%s:%s', $this->getHostname(), $this->getPort());

        if ($this->isRunning()) {
            throw new RuntimeException(sprintf('A process is already listening on http://%s.', $address));
        }

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
     * Check if a php build-in web server is running.
     *
     * @param string|null $pidFilePath
     *
     * @return bool
     */
    public function isRunning(?string $pidFilePath = null): bool
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
     * Returns the address if pid file exists.
     *
     * @param string|null $pidFilePath
     *
     * @return string|null
     */
    public function getAddress(?string $pidFilePath = null): ?string
    {
        $pidFilePath = $this->getPidFilePath($pidFilePath);

        if (! file_exists($pidFilePath)) {
            return null;
        }

        return file_get_contents($pidFilePath);
    }

    /**
     * Create a new Server process.
     *
     * @param string $documentRoot
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
            sprintf('%s:%s', $this->getHostname(), $this->getPort()),
            $this->getRouter(),
        ]);
        $process->setWorkingDirectory($documentRoot);
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
     * @param string $hostname
     *
     * @return int
     */
    private static function findBestPort(string $hostname): int
    {
        $port = 8000;

        while ($fp = @fsockopen($hostname, $port, $errno, $errstr, 1) !== false) {
            fclose($fp);

            if ($port++ >= 8100) {
                throw new RuntimeException('Unable to find a port available to run the web server.');
            }
        }

        return $port;
    }
}

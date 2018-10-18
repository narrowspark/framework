<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Viserio\Component\Contract\WebServer\Exception\RuntimeException;

final class WebServer
{
    public const STARTED = 0;

    public const STOPPED = 1;

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @param \Viserio\Component\WebServer\WebServerConfig $config
     * @param bool                                         $disableOutput
     * @param null|callable                                $callback
     *
     * @return void
     */
    public static function run(WebServerConfig $config, bool $disableOutput = true, callable $callback = null): void
    {
        if (self::isRunning()) {
            throw new RuntimeException(\sprintf('A process is already listening on http://%s.', $config->getAddress()));
        }

        $process = self::createServerProcess($config);

        if ($disableOutput) {
            $process->disableOutput();
            $callback = null;
        } else {
            try {
                $process->setTty(true);
                $callback = null;
            } catch (\RuntimeException $e) {
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
     * Starts a local web server in the background.
     *
     * @param \Viserio\Component\WebServer\WebServerConfig $config
     * @param null|string                                  $pidFile
     *
     * @return int
     */
    public static function start(WebServerConfig $config, string $pidFile = null): int
    {
        $pidFile = $pidFile ?? self::getDefaultPidFile();

        if (self::isRunning($pidFile)) {
            throw new RuntimeException(\sprintf('A process is already listening on http://%s.', $config->getAddress()));
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

        $process = self::createServerProcess($config);
        $process->disableOutput();
        $process->start();

        if (! $process->isRunning()) {
            throw new RuntimeException('Unable to start the server process.');
        }

        \file_put_contents($pidFile, $config->getAddress());

        // stop the web server when the lock file is removed
        while ($process->isRunning()) {
            if (! \file_exists($pidFile)) {
                $process->stop();
            }

            \sleep(1);
        }

        return self::STOPPED;
    }

    /**
     * Stop a running web server.
     *
     * @param null|string $pidFile
     *
     * @return void
     */
    public static function stop(string $pidFile = null): void
    {
        $pidFile = $pidFile ?? self::getDefaultPidFile();

        if (! \file_exists($pidFile)) {
            throw new RuntimeException('No web server is listening.');
        }

        \unlink($pidFile);
    }

    /**
     * Get the address from the pid file.
     *
     * @param null|string $pidFile
     *
     * @return bool|string
     */
    public static function getAddress(string $pidFile = null)
    {
        $pidFile = $pidFile ?? self::getDefaultPidFile();

        if (! \file_exists($pidFile)) {
            return false;
        }

        return \file_get_contents($pidFile);
    }

    /**
     * Check if a server is running.
     *
     * @param null|string $pidFile
     *
     * @return bool
     */
    public static function isRunning(string $pidFile = null): bool
    {
        $pidFile = $pidFile ?? self::getDefaultPidFile();

        if (! \file_exists($pidFile)) {
            return false;
        }

        $address  = \file_get_contents($pidFile);
        $pos      = \mb_strrpos($address, ':');
        $hostname = \mb_substr($address, 0, $pos);
        $port     = \mb_substr($address, $pos + 1);

        if (false !== $fp = @fsockopen($hostname, (int) $port, $errno, $errstr, 1)) {
            fclose($fp);

            return true;
        }

        \unlink($pidFile);

        return false;
    }

    /**
     * Create a new server command process.
     *
     * @param \Viserio\Component\WebServer\WebServerConfig $config
     *
     * @return \Symfony\Component\Process\Process
     */
    private static function createServerProcess(WebServerConfig $config): Process
    {
        $finder = new PhpExecutableFinder();

        if (($binary = $finder->find(false)) === false) {
            throw new RuntimeException('Unable to find the PHP binary.');
        }

        $xdebugArgs = [];

        if ($config->hasXdebug() && \extension_loaded('xdebug')) {
            $xdebugArgs = ['-dxdebug.profiler_enable_trigger=1'];
        }

        $process = new Process(\array_merge([$binary], $finder->findArguments(), $xdebugArgs, ['-dvariables_order=EGPCS', '-S', $config->getAddress(), $config->getRouter()]));
        $process->setWorkingDirectory($config->getDocumentRoot());
        $process->setTimeout(null);

        return $process;
    }

    /**
     * Get the default pid file path.
     *
     * @return string
     */
    private static function getDefaultPidFile(): string
    {
        return \getcwd() . \DIRECTORY_SEPARATOR . '.web-server-pid';
    }
}

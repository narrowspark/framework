<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverInvalidArgumentException;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException;
use Viserio\Component\Contract\WebServer\Exception\RuntimeException;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

final class WebServer implements
    RequiresConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract,
    RequiresValidatedConfigContract
{
    use OptionsResolverTrait;

    public const STARTED = 0;

    public const STOPPED = 1;

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'router'  => __DIR__ . '/Resources/router.php',
            'host'    => null,
            'port'    => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'document_root',
            'env',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'document_root' => static function ($value) {
                if (! \is_string($value)) {
                    throw OptionsResolverInvalidArgumentException::invalidType('document_root', $value, ['string'], self::class);
                }

                if (! \is_dir($value)) {
                    throw new OptionsResolverInvalidArgumentException(\sprintf('The document root directory [%s] does not exist.', $value));
                }
            },
            'router' => static function ($value) {
                if (! \is_string($value)) {
                    throw OptionsResolverInvalidArgumentException::invalidType('router', $value, ['string'], self::class);
                }

                if (\realpath($value) === false) {
                    throw new OptionsResolverInvalidArgumentException(\sprintf('Router script [%s] does not exist.', $value));
                }
            },
            'env'  => ['string'],
            'host' => ['string'],
            'port' => ['int', 'string'],
        ];
    }

    /**
     * @param array|\ArrayAccess $config
     * @param bool               $disableOutput
     * @param null|callable      $callback
     *
     * @return void
     */
    public static function run($config, bool $disableOutput = true, callable $callback = null): void
    {
        $config = self::prepareServerConfiguration($config);

        if (self::isRunning()) {
            throw new RuntimeException(\sprintf('A process is already listening on http://%s.', $config['address']));
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
     * @param array|\ArrayAccess $config
     * @param null|string        $pidFile
     *
     * @return int
     */
    public static function start($config, string $pidFile = null): int
    {
        $config = self::prepareServerConfiguration($config);

        $pidFile = $pidFile ?? self::getDefaultPidFile();

        if (self::isRunning($pidFile)) {
            throw new RuntimeException(\sprintf('A process is already listening on http://%s.', $config['address']));
        }

        $pid = \pcntl_fork();

        if ($pid < 0) {
            throw new RuntimeException('Unable to start the server process.');
        }

        if ($pid > 0) {
            return self::STARTED;
        }

        if (\posix_setsid() < 0) {
            throw new RuntimeException('Unable to set the child process as session leader.');
        }

        $process = self::createServerProcess($config);
        $process->disableOutput();
        $process->start();

        if (! $process->isRunning()) {
            throw new RuntimeException('Unable to start the server process.');
        }

        \file_put_contents($pidFile, $config['address']);

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
     * @param array|\ArrayAccess $config
     *
     * @return Process The process
     */
    private static function createServerProcess($config): Process
    {
        $finder = new PhpExecutableFinder();

        if (($binary = $finder->find(false)) === false) {
            throw new RuntimeException('Unable to find the PHP binary.');
        }

        $process = new Process(\array_merge([$binary], $finder->findArguments(), ['-dvariables_order=EGPCS', '-S', $config['address'], $config['router']]));
        $process->setWorkingDirectory($config['document_root']);
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
        return \getcwd() . DIRECTORY_SEPARATOR . '.web-server-pid';
    }

    /**
     * Finds the front controller in root path.
     *
     * @param string $documentRoot
     * @param string $env
     *
     * @throws \Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException
     *
     * @return string
     */
    private static function findFrontController(string $documentRoot, string $env): string
    {
        $fileNames = ['index_' . $env . '.php', 'index.php'];

        foreach ($fileNames as $fileName) {
            if (\file_exists($documentRoot . '/' . $fileName)) {
                return $fileName;
            }
        }

        throw new InvalidArgumentException(
            \sprintf(
                'Unable to find the front controller under "%s" (none of these files exist: %s).',
                $documentRoot,
                \implode(', ', $fileNames)
            )
        );
    }

    /**
     * Finds a host and port.
     *
     * @param array $config
     *
     * @throws \Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException
     *
     * @return array
     */
    private static function findHostnameAndPort(array $config): array
    {
        if ($config['host'] === null) {
            $config['host'] = '127.0.0.1';
            $config['port'] = self::findBestPort($config['host']);
        } elseif ($config['host'] !== null && $config['port'] !== null) {
            if ($config['host'] === '*') {
                $config['host'] = '0.0.0.0';
            }
        } elseif (\ctype_digit($config['port'])) {
            $config['host'] = '127.0.0.1';
        } else {
            $config['port'] = self::findBestPort($config['host']);
        }

        if (! \ctype_digit($config['port'])) {
            throw new InvalidArgumentException(\sprintf('Port [%s] is not valid.', $config['port']));
        }

        $config['address'] = $config['host'] . ':' . $config['port'];

        return $config;
    }

    /**
     * Searching for the port between 8000 and 8100.
     *
     * @param string $host
     *
     * @return int
     */
    private static function findBestPort(string $host): int
    {
        $port = 8000;

        while (false !== $fp = @\fsockopen($host, $port, $errno, $errstr, 1)) {
            \fclose($fp);

            if ($port++ >= 8100) {
                throw new RuntimeException('Unable to find a port available to run the web server.');
            }
        }

        return $port;
    }

    /**
     * Prepares configuration for the server.
     *
     * @param array|\ArrayAccess $config
     *
     * @throws \Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException
     *
     * @return array
     */
    private static function prepareServerConfiguration($config): array
    {
        $resolvedOptions = static::resolveOptions($config);

        $_ENV['APP_FRONT_CONTROLLER'] = self::findFrontController(
            $resolvedOptions['document_root'],
            $resolvedOptions['env']
        );

        return self::findHostnameAndPort($resolvedOptions);
    }
}

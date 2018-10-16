<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverInvalidArgumentException;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException;
use Viserio\Component\Contract\WebServer\Exception\RuntimeException;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

final class WebServerConfig implements
    RequiresConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract,
    RequiresValidatedConfigContract
{
    use OptionsResolverTrait;

    /**
     * Resolved options.
     *
     * @var array
     */
    private $resolvedOptions;

    /**
     * Create a new WebServerConfig instance.
     *
     * @param array|\ArrayAccess $config
     */
    public function __construct($config, AbstractCommand $command)
    {

        $resolvedOptions = self::resolveOptions($config);

        $this->resolvedOptions = self::findHostnameAndPort($resolvedOptions);

        $_ENV['APP_FRONT_CONTROLLER'] = self::findFrontController(
            $this->resolvedOptions['document_root'],
            $this->resolvedOptions['env']
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'router'  => __DIR__ . \DIRECTORY_SEPARATOR . 'Resources' . \DIRECTORY_SEPARATOR . 'router.php',
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
            'env'            => ['string'],
            'host'           => ['string'],
            'port'           => ['int', 'string'],
            'disable-xdebug' => ['bool'],
        ];
    }

    public function getDocumentRoot(): string
    {
        return $this->resolvedOptions['document_root'];
    }

    public function getEnv()
    {
        return $this->resolvedOptions['env'];
    }

    public function getRouter()
    {
        return $this->resolvedOptions['router'];
    }

    public function getHostname()
    {
        return $this->resolvedOptions['host'];
    }

    public function getPort()
    {
        return $this->resolvedOptions['port'];
    }

    public function getAddress()
    {
        return $this->resolvedOptions['address'];
    }

    public function hasXdebug(): bool
    {
        return $this->resolvedOptions['disable-xdebug'] === false;
    }

    /**
     * Contains resolved hostname if available.
     *
     * @return null|string
     */
    public function getDisplayAddress(): ?string
    {
        if ('0.0.0.0' !== $this->getHostname()) {
            return null;
        }

        if (false === $localHostname = \gethostname()) {
            return null;
        }

        return \gethostbyname($localHostname) . ':' . $this->getPort();
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
            if (\file_exists($documentRoot . \DIRECTORY_SEPARATOR . $fileName)) {
                return $fileName;
            }
        }

        throw new InvalidArgumentException(
            \sprintf(
                'Unable to find the front controller under [%s] (none of these files exist: [%s]).',
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
     * Prepare the config for the web server.
     *
     * @return array
     */
    private function prepareConfig(): array
    {
        $config = [
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

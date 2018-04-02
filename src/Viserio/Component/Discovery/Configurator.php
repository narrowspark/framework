<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery;

use Composer\Composer;
use Composer\IO\IOInterface;
use Viserio\Component\Discovery\Configurator\AbstractConfigurator;
use Viserio\Component\Discovery\Configurator\ComposerScriptsConfigurator;
use Viserio\Component\Discovery\Configurator\CopyFromPackageConfigurator;
use Viserio\Component\Discovery\Configurator\EnvConfigurator;
use Viserio\Component\Discovery\Configurator\GitIgnoreConfigurator;
use Viserio\Component\Discovery\Configurator\ServiceProviderConfigurator;

final class Configurator
{
    /**
     * @var array
     */
    public static $configurators = [
        'composer_script' => ComposerScriptsConfigurator::class,
        'copy'            => CopyFromPackageConfigurator::class,
        'env'             => EnvConfigurator::class,
        'git_ignore'      => GitIgnoreConfigurator::class,
        'providers'       => ServiceProviderConfigurator::class,
    ];

    /**
     * Cache found configurators form manifest.
     *
     * @var array
     */
    private $cache = [];

    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    /**
     * Create a new Configurator class.
     *
     * @param \Composer\Composer       $composer
     * @param \Composer\IO\IOInterface $io
     */
    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io       = $io;
    }

    /**
     * Add a new Discovery Configurator.
     *
     * @param string $name
     * @param string $configurator
     *
     * @return void
     */
    public function add(string $name, string $configurator): void
    {
        if (isset(self::$configurators[$name])) {
            throw new \InvalidArgumentException(sprintf('Configurator with the name "%s" already exists.', $name));
        }

        if (! \is_subclass_of($configurator, AbstractConfigurator::class)) {
            throw new \InvalidArgumentException(\sprintf('Configurator class "%s" must extend the class "%s".', $configurator, AbstractConfigurator::class));
        }

        static::$configurators[$name] = $configurator;
    }

    /**
     * @param Package $package
     *
     * @return void
     */
    public function configure(Package $package): void
    {
        foreach (\array_keys(self::$configurators) as $key) {
            if ($package->hasConfiguratorKey($key, Package::CONFIGURE)) {
                $this->get($key)->configure($package);
            }
        }
    }

    /**
     * @param array $manifest
     *
     * @return void
     */
    public function unconfigure(array $manifest): void
    {
        foreach (array_keys(self::$configurators) as $key) {
            if (isset($manifest[$key])) {
                $this->get($key)->unconfigure($manifest[$key]);
            }
        }
    }

    /**
     * @param string $key
     *
     * @return \Viserio\Component\Discovery\Configurator\AbstractConfigurator
     */
    private function get(string $key): AbstractConfigurator
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $class = self::$configurators[$key];

        return $this->cache[$key] = new $class($this->composer, $this->io, $this->options);
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Discovery\Configurator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Viserio\Component\Foundation\Discovery\Util\Path;

abstract class AbstractConfigurator
{
    /**
     * @var \Composer\Composer
     */
    protected $composer;

    /**
     * @var \Composer\IO\IOInterface
     */
    protected $io;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Path $path
     */
    protected $path;

    /**
     * AbstractConfigurator constructor.
     *
     * @param \Composer\Composer       $composer
     * @param \Composer\IO\IOInterface $io
     * @param array                    $options
     */
    public function __construct(Composer $composer, IOInterface $io, array $options = [])
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->options = $options;
        $this->path = new Path(getcwd());
    }

    /**
     * @param array $config
     *
     * @return void
     */
    abstract public function configure(array $config): void;

    /**
     * @param array $config
     *
     * @return void
     */
    abstract public function unconfigure(array $config): void;
}

<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Engine;

use Twig_Loader_Array;
use Viserio\Component\View\Engines\TwigEngine as BaseTwigEngine;
use Viserio\Bridge\Twig\Loader as TwigLoader;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;

class TwigEngine extends BaseTwigEngine
{
    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Viserio\Component\Contracts\View\Finder
     */
    protected $finder;

    /**
     * Create a new twig view instance.
     *
     * @param array                                              $config
     * @param \Viserio\Component\Contracts\Filesystem\Filesystem $files
     * @param \Viserio\Component\Contracts\View\Finder           $finder
     */
    public function __construct(array $config, FilesystemContract $files, FinderContract $finder)
    {
        $this->config = $config;
        $this->files  = $files;
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLoader(): Twig_LoaderInterface
    {
        $config = $this->config;

        $loader = new Twig_Loader_Chain([
            new Twig_Loader_Array($config['templates'] ?? []),
            new TwigLoader($this->files, $this->finder = $finder, $config['extension'] ?? 'twig'),
        ]);

        if (($paths = $config['paths'] ?? null) !== null) {
            foreach ($paths as $name => $path) {
                $loader->addPath($path, $name);
            }
        }

        return $loader;
    }
}

<?php
declare(strict_types=1);
namespace Viserio\View\Engines;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Viserio\Contracts\View\Engine as EngineContract;

class TwigEngine implements EngineContract
{
    /**
     * Config array.
     *
     * @var array
     */
    protected $config;

    /**
     * The Twig environment for rendering templates.
     *
     * @var \Twig_Environment
     */
    protected $parserInstance;

    /**
     * Create a new twig view instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        return $this->getInstance()->render($fileInfo['name'], $data);
    }

    /**
     * Creates new Twig_Environment if it doesn't already exist, and returns it.
     *
     * @return \Twig_Environment
     */
    protected function getInstance(): Twig_Environment
    {
        if (! $this->parserInstance) {
            $config = $this->config['engine']['twig'] ?? [];
            $twig   = new Twig_Environment(
                $this->loader(),
                $config['options'] ?? []
            );

            // @codeCoverageIgnoreStart
            if (($extensions = $config['extensions'] ?? null) !== null) {
                foreach ($extensions as $extension) {
                    $twig->addExtension(is_object($extension) ? $extension : new $extension());
                }
            }
            // @codeCoverageIgnoreEnd

            $this->parserInstance = $twig;
        }

        return $this->parserInstance;
    }

    /**
     * Twig paths loader.
     *
     * @return \Twig_Loader_Filesystem
     */
    protected function loader(): Twig_Loader_Filesystem
    {
        $config = $this->config['template'] ?? [];
        $loader = new Twig_Loader_Filesystem($config['default'] ?? []);

        if (($paths = $config['paths'] ?? null) !== null) {
            foreach ($paths as $name => $path) {
                $loader->addPath($path, $name);
            }
        }

        return $loader;
    }
}

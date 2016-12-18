<?php
declare(strict_types=1);
namespace Viserio\View\Engines;

use Throwable;
use Twig_Environment;
use Twig_Extension_Core;
use Twig_Extension_Optimizer;
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
        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();

        try {
            return $this->getInstance()->render($fileInfo['name'], $data);
        } catch (Throwable $exception) {
            $this->handleViewException($exception);
        }
    }

    /**
     * Creates new Twig_Environment if it doesn't already exist, and returns it.
     *
     * @return \Twig_Environment
     */
    protected function getInstance(): Twig_Environment
    {
        if (! $this->parserInstance) {
            $config = $this->config;
            $twig   = new Twig_Environment(
                $this->loader(),
                $config['engine']['twig']['options'] ?? []
            );

            $twig->addExtension(new Twig_Extension_Core());
            $twig->addExtension(new Twig_Extension_Optimizer());

            $extensions = $config['engine']['twig']['extensions'] ?? [];

            if (! empty($extensions)) {
                foreach ($extensions as $extension) {
                    $twig->addExtension(is_object($extension) ? $extension : new $extension());
                }
            }

            $this->parserInstance = $twig;
        }

        return $this->parserInstance;
    }

    /**
     * Twig paths loader.
     */
    protected function loader()
    {
        $config = $this->config;
        $loader = new Twig_Loader_Filesystem($config['template']['default'] ?? []);

        if (($paths = $config['template']['paths'] ?? []) !== null) {
            foreach ($paths as $name => $path) {
                $loader->addPath($path, $name);
            }
        }

        return $loader;
    }

    /**
     * Handle a view exception.
     *
     * @param \Throwable $exception
     *
     * @throws \Throwable
     */
    protected function handleViewException(Throwable $exception)
    {
        ob_end_clean();

        throw $exception;
    }
}

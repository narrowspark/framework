<?php
declare(strict_types=1);
namespace Viserio\View\Engines\Adapter;

use ErrorException;
use Twig_Extension_Optimizer;
use Twig_Extension_Core;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Throwable;
use Viserio\Contracts\View\Engine as EngineContract;

class Twig implements EngineContract
{
    /**
     * Config manager instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * The Twig environment for rendering templates.
     *
     * @var \TwigEnvironment
     */
    protected $parserInstance;

    /**
     * Create a new twig view instance.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     */
    public function __construct(ManagerContract $config)
    {
        $this->config = $config;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    public function get(string $path, array $data = []): string
    {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param string $phpPath
     * @param array  $data
     *
     * @return string
     */
    protected function evaluatePath(string $path, array $data): string
    {
        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();

        try {
            return $this->getInstance()->render($template, $data);
        } catch (Throwable $exception) {
            $this->handleViewException($exception);
        }
    }

    /**
     * Creates new TwigEnvironment if it doesn't already exist, and returns it.
     *
     * @return \Twig_Environment
     */
    protected function getInstance(): Twig_Environment
    {
        if (!$this->parserInstance) {
            $config = $this->config;
            $twig = new Twig_Environment(
                $this->loader(),
                $config->get('view.engine.twig.options', [])
            );

            $twig->addExtension(new Twig_Extension_Core());
            $twig->addExtension(new Twig_Extension_Optimizer());
            $extensions = $config->get('view.engine.twig.extensions', []);

            if (!empty($extensions)) {
                foreach ($extensions as $extension) {
                    $twig->addExtension(is_object($extension) ? $extension : new $extension);
                }
            }
        }

        return $this->parserInstance;
    }

    /**
     * Twig paths loader.
     */
    protected function loader()
    {
        $config = $this->config;
        $defaultPath = $config->get('view.default.template.path', null);
        $loader = new Twig_Loader_Filesystem((array) $defaultPath);

        if (($paths = $config->get('view.template.paths', [])) !== null) {
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
     * @param int        $obLevel
     *
     * @throws \Throwable
     */
    protected function handleViewException(Throwable $exception, int $obLevel)
    {
        ob_end_clean();

        throw $exception;
    }
}

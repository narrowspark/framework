<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use ErrorException;
use Twig_Error_Loader;
use Twig_Error;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Viserio\Component\Contracts\View\Engine as EngineContract;

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
        try {
            $content = $this->getInstance()->render($fileInfo['name'], $data);
        } catch (Twig_Error $exception) {
            $this->handleTwigError($exception);
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
            $config = $this->config['engine']['twig'] ?? [];
            $twig   = new Twig_Environment(
                $this->getLoader(),
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
    protected function getLoader(): Twig_Loader_Filesystem
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

    /**
     * Handle a TwigError exception.
     *
     * @param \Twig_Error $exception
     *
     * @throws \Twig_Error|\ErrorException
     */
    protected function handleTwigError(Twig_Error $exception)
    {
        $templateFile = $exception->getTemplateFile();
        $templateLine = $exception->getTemplateLine();
        $file         = null;

        if ($templateFile && file_exists($templateFile)) {
            $file = $templateFile;
        }

        if ($file !== null) {
            $exception = new ErrorException(
                $exception->getMessage(),
                0,
                1,
                $file,
                $templateLine,
                $exception
            );
        }

        throw $exception;
    }
}

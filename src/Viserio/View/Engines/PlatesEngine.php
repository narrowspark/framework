<?php
declare(strict_types=1);
namespace Viserio\View\Engines;

use Exception;
use League\Plates\Engine as LeagueEngine;
use League\Plates\Extension\Asset;
use League\Plates\Extension\URI;
use League\Plates\Template\Template;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Contracts\View\Engine as EnginesContract;

class PlatesEngine implements EnginesContract
{
    /**
     * Config array.
     *
     * @var array
     */
    protected $config;

    /**
     * Engine instance.
     *
     * @var \League\Plates\Engine
     */
    protected $engine;

    /**
     * Server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * All available extensions.
     *
     * @var array
     */
    protected $availableExtensions = [];

    /**
     * Create a new plates view instance.
     *
     * @param array                                         $config
     * @param \Psr\Http\Message\ServerRequestInterface|null $request
     */
    public function __construct(array $config, ServerRequestInterface $request = null)
    {
        $this->config  = $config;
        $this->request = $request;

        $exceptions = $this->config['engine']['plates']['extensions'] ?? null;

        if ($exceptions !== null) {
            $this->availableExtensions = $exceptions;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        $engine = $this->getLoader();

        if ($this->request !== null) {
            // Set uri extensions
            $engine->loadExtension(new URI($this->request->getUri()->getPath()));
        }

        // Set asset extensions
        $engine->loadExtension(new Asset($this->config['engine']['plates']['asset'] ?? null));

        // Get all extensions
        if (! empty($this->availableExtensions)) {
            foreach ($this->availableExtensions as $extension) {
                $engine->loadExtension(is_object($extension) ? $extension : new $extension());
            }
        }

        if (! $engine->exists($fileInfo['name'])) {
            throw new Exception('Template "' . $fileInfo['name'] . '" dont exist!');
        }

        // Creat a new template
        $template = new Template($engine, $fileInfo['name']);

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();

        try {
            $template = $template->render($data);
        } catch (Throwable $exception) {
            $this->handleViewException($exception);
        }

        // @codeCoverageIgnoreStart
        // Return temporary output buffer content, destroy output buffer
        ltrim(ob_get_clean());
        // @codeCoverageIgnoreEnd

        return $template;
    }

    /**
     * Plates paths loader.
     */
    protected function getLoader(): LeagueEngine
    {
        if (! $this->engine) {
            $config = $this->config;

            $this->engine = new LeagueEngine(
                $config['template']['default'] ?? null,
                $config['engine']['plates']['file_extension'] ?? null
            );

            if (($paths = $config['template']['paths'] ?? null) !== null) {
                foreach ($paths as $name => $addPaths) {
                    $this->engine->addFolder($name, $addPaths);
                }
            }
        }

        return $this->engine;
    }

    /**
     * Handle a view exception.
     *
     * @param \Throwable $exception
     *
     * @throws $exception
     */
    protected function handleViewException(Throwable $exception)
    {
        ob_get_clean();

        throw $exception;
    }
}

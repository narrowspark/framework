<?php
declare(strict_types=1);
namespace Viserio\View\Engines;

use Exception;
use League\Plates\Engine as LeagueEngine;
use League\Plates\Extension\Asset;
use League\Plates\Extension\URI;
use League\Plates\Template\Template;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
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
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $fileInfo, array $data = []): string
    {
        $engine = $this->getLoader();
        $config = $this->config['engine']['plates'] ?? [];

        if ($this->request !== null) {
            // Set uri extensions
            $engine->loadExtension(new URI($this->request->getUri()->getPath()));
        }

        // Set asset extensions
        $engine->loadExtension(new Asset($config['asset'] ?? null));

        // Get all extensions
        if (($exceptions = $config['extensions'] ?? null) !== null) {
            foreach ($exceptions as $extension) {
                if (is_object($extension)) {
                    $engine->loadExtension($extension);
                } else {
                    throw new RuntimeException(sprintf(
                        'Plates extension [%s => %s] is not a object.',
                        (string) $extension,
                        gettype($extension)
                    ));
                }
            }
        }

        if (! $engine->exists($fileInfo['name'])) {
            throw new Exception(sprintf('Template [%s] dont exist!', $fileInfo['name']));
        }

        // Creat a new template
        $template = new Template($engine, $fileInfo['name']);

        return $template->render($data);
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
                foreach ((array) $paths as $name => $addPaths) {
                    $this->engine->addFolder($name, $addPaths);
                }
            }
        }

        return $this->engine;
    }
}

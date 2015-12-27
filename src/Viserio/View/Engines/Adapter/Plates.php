<?php
namespace Viserio\View\Engines\Adapter;

use Interop\Container\ContainerInterface as ContainerContract;
use League\Plates\Engine;
use League\Plates\Extension\Asset;
use League\Plates\Extension\URI;
use League\Plates\Template\Template;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\View\Engine as EnginesContract;

class Plates implements EnginesContract
{
    /**
     * Container.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * [$engine description].
     *
     * @var [type]
     */
    protected $engine;

    /**
     * Config instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * All available extensions.
     *
     * @var array
     */
    protected $availableExtensions = [];

    /**
     * Create a new view environment instance.
     *
     * @param ContainerContract $container
     */
    public function __construct(ContainerContract $container)
    {
        $this->container = $container;

        $exceptions = $this->container->get('config')->get('view::plates.extensions', null);

        if ($exceptions !== null) {
            $this->availableExtensions = $exceptions;
        }

        //Engine
        $this->loader();
    }

    /**
     * Plates paths.
     */
    protected function loader()
    {
        $engine = new Engine($this->container->get('config')->get('view::default.template.path', null));

        if ($this->container->get('config')->get('view::template.paths', null) !== null) {
            foreach ($this->container->get('config')->get('view::template.paths', null) as $name => $addPaths) {
                $engine->addFolder($name, $addPaths);
            }
        }

        $engine->setFileExtension(null);

        // Engine
        $this->engine = $engine;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Load needed config to engine.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     */
    public function setConfig(ConfigContract $config)
    {
        $this->config = $config;
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param string $path
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function evaluatePath($path, array $data)
    {
        $engine = $this->engine;

        // Set uri extensions
        $engine->loadExtension(new URI($this->container->get('request')->getPathInfo()));

        // Set asset extensions
        $engine->loadExtension(new Asset($this->container->get('config')->get('view::asset', null)));

        // Get all extensions
        if ($this->container->get('config')->get('view::plates.extensions', null) !== null) {
            foreach ($this->availableExtensions as $ext) {
                $engine->loadExtensions($ext);
            }
        }

        // Creat a new template
        $template = new Template($engine, $path);

        if (!$engine->exists($path)) {
            throw new \Exception('Template "' . $path . '" dont exist!');
        }

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();

        try {
            return $template->render($data);
        } catch (\Exception $exception) {
            $this->handleViewException($exception);
        }
    }

    /**
     * Handle a view exception.
     *
     * @param \Exception $exception
     *
     * @throws $exception
     */
    protected function handleViewException(\Exception $exception)
    {
        ob_get_clean();
        throw $exception;
    }
}

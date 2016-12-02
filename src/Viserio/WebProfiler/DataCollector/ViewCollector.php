<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Viserio\Contracts\View\View as ViewContract;
use Symfony\Component\HttpKernel\DataCollector\Util\ValueExporter;

class ViewCollector extends DataCollector implements Renderable, AssetProvider
{
    /**
     * Array of all templates.
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Collects view data when true.
     *
     * @var bool|null
     */
    protected $collectData;

    /**
     * Create a ViewCollector.
     *
     * @param bool $collectData
     */
    public function __construct(bool $collectData = true)
    {
        $this->collectData = $collectData;
        $this->exporter = new ValueExporter();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'views';
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgets()
    {
        return [
            'views' => [
                'icon' => 'leaf',
                'widget' => 'PhpDebugBar.Widgets.TemplatesWidget',
                'map' => 'views',
                'default' => '[]'
            ],
            'views:badge' => [
                'map' => 'views.nb_templates',
                'default' => 0
            ]
        ];
    }

    public function collect()
    {
        $templates = $this->templates;

        return [
            'nb_templates' => count($templates),
            'templates' => $templates,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets()
    {
        return [
            'css' => 'widgets/templates/widget.css',
            'js' => 'widgets/templates/widget.js'
        ];
    }

    /**
     * Add a View instance to the Collector
     *
     * @param \Viserio\Contracts\View\View $view
     */
    public function addView(ViewContract $view)
    {
        $name = $view->getName();
        $path = $view->getPath();

        if (! is_object($path)) {
            if ($path) {
                $path = ltrim(str_replace('@ToDO add basepath', '', realpath($path)), '/');
            }

            $type = $view->getExtension();

            if ($type === null) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
            }

        } else {
            $type = get_class($view);
            $path = '';
        }

        if (!$this->collect_data) {
            $params = array_keys($view->getData());
        } else {
            $data = [];

            foreach ($view->getData() as $key => $value) {
                $data[$key] = $this->exporter->exportValue($value);
            }

            $params = $data;
        }

        $template = [
            'name' => $path ? sprintf('%s (%s)', $name, $path) : $name,
            'param_count' => count($params),
            'params' => $params,
            'type' => $type,
        ];

        if ($this->getXdebugLink($path)) {
            $template['xdebug_link'] = $this->getXdebugLink($path);
        }


        $this->templates[] = $template;
    }

    /**
     * Get an Xdebug Link to a file
     *
     * @return string|null
     */
    public function getXdebugLink($file, $line = 1)
    {
        if (ini_get('xdebug.file_link_format') || extension_loaded('xdebug')) {
            return e(str_replace(array('%f', '%l'), array($file, $line), ini_get('xdebug.file_link_format')));
        }
    }
}

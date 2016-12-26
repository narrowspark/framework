<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use Viserio\Contracts\Support\Renderable as RenderableContract;
use Viserio\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class AssetsRenderer implements RenderableContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * All css files.
     *
     * @var array
     */
    protected $cssFiles = [
        'css/webprofiler.css',
        'css/webprofiler-grid.css',
    ];

    /**
     * All js files.
     *
     * @var array
     */
    protected $jsFiles = [
        'js/zepto.min.js',
        'js/webprofiler.js',
    ];

    /**
     * List of all icons.
     *
     * @var array
     */
    protected $icons = [
        'ic_clear_white_24px.svg'               => __DIR__ . '/Resources/icons/ic_clear_white_24px.svg',
        'ic_memory_white_24px.svg'              => __DIR__ . '/Resources/icons/ic_memory_white_24px.svg',
        'ic_message_white_24px.svg'             => __DIR__ . '/Resources/icons/ic_message_white_24px.svg',
        'ic_narrowspark_white_24px.svg'         => __DIR__ . '/Resources/icons/ic_narrowspark_white_24px.svg',
        'ic_schedule_white_24px.svg'            => __DIR__ . '/Resources/icons/ic_schedule_white_24px.svg',
        'ic_storage_white_24px.svg'             => __DIR__ . '/Resources/icons/ic_storage_white_24px.svg',
        'ic_mail_outline_white_24px.svg'        => __DIR__ . '/Resources/icons/ic_mail_outline_white_24px.svg',
        'ic_keyboard_arrow_up_white_24px.svg'   => __DIR__ . '/Resources/icons/ic_keyboard_arrow_up_white_24px.svg',
        'ic_keyboard_arrow_down_white_24px.svg' => __DIR__ . '/Resources/icons/ic_keyboard_arrow_down_white_24px.svg',
        'ic_repeat_white_24px.svg'              => __DIR__ . '/Resources/icons/ic_repeat_white_24px.svg',
    ];

    /**
     * The webprofiler instance.
     *
     * @var \Viserio\Contracts\WebProfiler\WebProfiler
     */
    protected $webprofiler;

    /**
     * [$rootPath description].
     *
     * @var string
     */
    protected $rootPath;

    /**
     * List of ignored collectors.
     *
     * @var array
     */
    protected $ignoredCollectors = [];

    /**
     * jQuery is used, remove zapto js.
     *
     * @var bool
     */
    protected $jqueryIsUsed;

    /**
     * Create a new file javascript renderer instance.
     *
     * @param bool        $jqueryIsUsed
     * @param string|null $rootPath
     */
    public function __construct(bool $jqueryIsUsed = false, string $rootPath = null)
    {
        $this->jqueryIsUsed = $jqueryIsUsed;
        $this->rootPath     = $rootPath ?? __DIR__ . '/Resources';
    }

    /**
     * Set the WebProfiler.
     *
     * @param \Viserio\Contracts\WebProfiler\WebProfiler $webprofiler
     *
     * @return $this
     */
    public function setWebProfiler(WebProfilerContract $webprofiler): self
    {
        $this->webprofiler = $webprofiler;

        return $this;
    }

    /**
     * Add icon to list.
     *
     * @param string $name
     * @param string $path
     *
     * @return $this
     */
    public function setIcon(string $name, string $path): self
    {
        $this->icons[$name] = self::normalizePath($path . '/' . $name);

        return $this;
    }

    /**
     * Get all registered icons.
     *
     * @return array
     */
    public function getIcons(): array
    {
        return $this->icons;
    }

    /**
     * Ignores widgets provided by a collector.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setIgnoredCollector(string $name)
    {
        $this->ignoredCollectors[] = $name;

        return $this;
    }

    /**
     * Returns the list of ignored collectors.
     *
     * @return array
     */
    public function getIgnoredCollectors(): array
    {
        return $this->ignoredCollectors;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        if (($urlGenerator = $this->webprofiler->getUrlGenerator()) !== null) {
            $cssRoute = $urlGenerator->route('webprofiler.assets.css', [
                'v' => $this->getModifiedTime('css'),
            ]);
            $jsRoute = $urlGenerator->route('webprofiler.assets.js', [
                'v' => $this->getModifiedTime('js'),
            ]);

            $html = sprintf(
                '<link rel="stylesheet" type="text/css" property="stylesheet" href="%s">',
                preg_replace('/\Ahttps?:/', '', $cssRoute)
            );
            $html .= sprintf(
                '<script type="text/javascript" src="{$jsRoute}"></script>',
                preg_replace('/\Ahttps?:/', '', $jsRoute)
            );

            return $html;
        }

        return $this->renderIntoHtml();
    }

    /**
     * Return assets as a string.
     *
     * @param string $type 'js' or 'css'
     *
     * @return string
     */
    public function dumpAssetsToString(string $type): string
    {
        $files   = $this->getAssets($type);
        $content = '';

        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }

    /**
     * Returns the list of asset files.
     *
     * @param string $type Only return css or js files
     *
     * @return array
     */
    public function getAssets(string $type = ''): array
    {
        $cssFiles = array_map(
            function ($css) {
                return rtrim($this->rootPath, '/') . '/' . $css;
            },
            $this->cssFiles
        );

        if ($this->jqueryIsUsed) {
            $this->jsFiles = array_diff($this->jsFiles, ['js/zepto.min.js']);
        }

        $jsFiles = array_map(
            function ($js) {
                return rtrim($this->rootPath, '/') . '/' . $js;
            },
            $this->jsFiles
        );

        $additionalAssets = [];

        // finds assets provided by collectors
        foreach ($this->webprofiler->getCollectors() as $collector) {
            if ($collector instanceof AssetAwareContract &&
                ! in_array($collector->getName(), $this->ignoredCollectors)
            ) {
                $additionalAssets[] = $collector->getAssets();
            }
        }

        foreach ($additionalAssets as $assets) {
            if (isset($assets['css'])) {
                $cssFiles = array_merge($cssFiles, (array) $assets['css']);
            }

            if (isset($assets['js'])) {
                $jsFiles = array_merge($jsFiles, (array) $assets['js']);
            }
        }

        return $this->filterAssetArray([$cssFiles, $jsFiles], $type);
    }

    /**
     * Render css and js into html elements.
     *
     * @return string
     */
    protected function renderIntoHtml(): string
    {
        $html = '<style>' . $this->dumpAssetsToString('css') . '</style>';
        $html .= "<script type='text/javascript'>" . $this->dumpAssetsToString('js') . '</script>';

        return $html;
    }

    /**
     * Filters a tuple of (css, js) assets according to $type.
     *
     * @param array  $array
     * @param string $type  'css', 'js' or null for both
     *
     * @return array
     */
    protected function filterAssetArray(array $array, string $type = ''): array
    {
        $type = mb_strtolower($type);

        if ($type === 'css') {
            return $array[0];
        }

        if ($type === 'js') {
            return $array[1];
        }

        return $array;
    }

    /**
     * Get the last modified time of any assets.
     *
     * @param string $type 'js' or 'css'
     *
     * @return int
     */
    protected function getModifiedTime(string $type): int
    {
        $files  = $this->getAssets($type);
        $latest = 0;

        foreach ($files as $file) {
            $mtime = filemtime($file);

            if ($mtime > $latest) {
                $latest = $mtime;
            }
        }

        return $latest;
    }
}

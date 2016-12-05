<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Contracts\Support\Renderable as RenderableContract;

class AssetsRenderer implements RenderableContract
{
    /**
     * All css files.
     *
     * @var array
     */
    protected $cssFiles = [
        'css/webprofiler.css',
    ];

    /**
     * All js files.
     *
     * @var array
     */
    protected $jsFiles = [
        'js/vue.min.js'
    ];

    /**
     * The debugbar instance.
     *
     * @var \Viserio\Contracts\WebProfiler\WebProfiler
     */
    protected $webprofiler;

    /**
     * [$rootPath description]
     *
     * @var string
     */
    protected $rootPath;

    /**
     * Additional assets form collectors.
     *
     * @var array
     */
    protected $additionalAssets = [];

    /**
     * List of ignored collectors.
     *
     * @var array
     */
    protected $ignoredCollectors = [];

    /**
     * Create a new file javascript renderer instance.
     *
     * @param \Viserio\Contracts\WebProfiler\WebProfiler $webprofiler
     * @param string|null                                $rootPath
     */
    public function __construct(WebProfilerContract $webprofiler, string $rootPath = null) {
        $this->webprofiler = $webprofiler;
        $this->rootPath = $rootPath ?? __DIR__ . '/Resources';
    }

    /**
     * Ignores widgets provided by a collector
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

            $cssRoute = preg_replace('/\Ahttps?:/', '', $cssRoute);
            $jsRoute = preg_replace('/\Ahttps?:/', '', $jsRoute);

            $html = "<link rel='stylesheet' type='text/css' property='stylesheet' href='{$cssRoute}'>";
            $html .= "<script type='text/javascript' src='{$jsRoute}'></script>";

            return $html;
        }

        return $this->renderIntoHtml();
    }

    /**
     * Return assets as a string
     *
     * @param string $type 'js' or 'css'
     *
     * @return string
     */
    public function dumpAssetsToString(string $type): string
    {
        $files = $this->getAssets($type);
        $content = '';

        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }

    /**
     * Returns the list of asset files
     *
     * @param string|null $type Only return css or js files
     *
     * @return array
     */
    public function getAssets(string $type = null): array
    {
        $cssFiles = array_map(
            function($css) {
                return rtrim($this->rootPath, '/') . '/' . $css;
            },
            $this->cssFiles
        );
        $jsFiles = array_map(
            function($css) {
                return rtrim($this->rootPath, '/') . '/' . $css;
            },
            $this->jsFiles
        );

        $additionalAssets = $this->additionalAssets;

        // finds assets provided by collectors
        foreach ($this->webprofiler->getCollectors() as $collector) {
            if (($collector instanceof AssetProvider) && !in_array($collector->getName(), $this->ignoredCollectors)) {
                $additionalAssets[] = $collector->getAssets();
            }
        }

        foreach ($additionalAssets as $assets) {
            $root = $assets['path'] ?? $this->rootPath;

            $cssFiles = array_merge($cssFiles, array_map(
                function($css) use($root) {
                    return rtrim($root, '/') . '/' . $css;
                },
                (array) $assets['css']
            ));
            $jsFiles = array_merge($jsFiles, array_map(
                function($css) use($root) {
                    return rtrim($root, '/') . '/' . $css;
                },
                (array) $assets['js']
            ));
        }

        return $this->filterAssetArray(array($cssFiles, $jsFiles), $type);
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
     * Filters a tuple of (css, js) assets according to $type
     *
     * @param array       $array
     * @param string|null $type 'css', 'js' or null for both
     *
     * @return array
     */
    protected function filterAssetArray(array $array, string $type = null): array
    {
        $type = strtolower($type);

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
        $files = $this->getAssets($type);
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

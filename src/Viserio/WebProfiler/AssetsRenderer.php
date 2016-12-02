<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use DebugBar\JavascriptRenderer as BaseJavascriptRenderer;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class JavascriptRenderer extends BaseJavascriptRenderer
{
    // Use XHR handler by default, instead of jQuery
    protected $ajaxHandlerBindToJquery = false;
    protected $ajaxHandlerBindToXHR = true;

    /**
     * Needed css files.
     *
     * @var array
     */
    protected $cssFiles = [
        __DIR__ . '/Resources/css/webprofiler.css',
        'widgets.css',
        'openhandler.css',
    ];

    /**
     * Needed js files.
     *
     * @var array
     */
    protected $jsFiles = [
        __DIR__ . '/Resources/js/jquery-3.1.1.min.js',
        'debugbar.js',
        __DIR__ . '/Resources/js/widgets.js',
        'openhandler.js',
    ];

    /**
     * The debugbar instance.
     *
     * @var \Viserio\Contracts\WebProfiler\WebProfiler
     */
    protected $webprofiler;

    /**
     * Create a new file javascript renderer instance.
     *
     * @param \Viserio\Contracts\WebProfiler\WebProfiler $webprofiler
     * @param string|null                                $baseUrl
     * @param string|null                                $basePath
     */
    public function __construct(
        WebProfilerContract $webprofiler,
        string $baseUrl = null,
        string $basePath = null
    ) {
        parent::__construct($webprofiler, $baseUrl, $basePath);

        $this->webprofiler = $webprofiler;
    }

    /**
     * {@inheritdoc}
     */
    public function renderHead()
    {
        if (($urlGenerator = $this->webprofiler->getUrlGenerator()) !== null) {
            $cssRoute = $urlGenerator->route('webprofiler.assets.css', [
                'v' => $this->getModifiedTime('css'),
            ]);
            $jsRoute = $urlGenerator->route('webprofiler.assets.js', [
                'v' => $this->getModifiedTime('js'),
            ]);

            $cssRoute = preg_replace('/\Ahttps?:/', '', $cssRoute);
            $jsRoute  = preg_replace('/\Ahttps?:/', '', $jsRoute);

            $html  = "<link rel='stylesheet' type='text/css' property='stylesheet' href='{$cssRoute}'>";
            $html .= "<script type='text/javascript' src='{$jsRoute}'></script>";
        } else {
            $html = $this->renderIntoHtml();
        }

        if ($this->isJqueryNoConflictEnabled()) {
            $html .= '<script type="text/javascript">jQuery.noConflict(true);</script>' . "\n";
        }

        return $html;
    }

    /**
     * Return assets as a string
     *
     * @param string $type 'js' or 'css'
     *
     * @return string
     */
    public function dumpAssetsToString($type)
    {
        $files = $this->getAssets($type);
        $content = '';

        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        return $content;
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

    /**
     * Render css and js into html elements.
     *
     * @return string
     */
    protected function renderIntoHtml(): string
    {
        $html  = '<style>' . $this->dumpAssetsToString('css') . '</style>';
        $html .= "<script type='text/javascript'>" . $this->dumpAssetsToString('js') . '</script>';

        return $html;
    }
}

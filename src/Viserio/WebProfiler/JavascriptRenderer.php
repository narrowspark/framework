<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer as BaseJavascriptRenderer;
use Viserio\Contracts\Routing\Router as RouterContract;

class JavascriptRenderer extends BaseJavascriptRenderer
{
    // Use XHR handler by default, instead of jQuery
    protected $ajaxHandlerBindToJquery = false;
    protected $ajaxHandlerBindToXHR = true;

    protected $cssFiles = ['widgets.css', 'openhandler.css'];

    /**
     * The debugbar instance.
     *
     * @var \DebugBar\DebugBar
     */
    protected $webprofiler;

    /**
     * Create a new file javascript renderer instance.
     *
     * @param \DebugBar\DebugBar $webprofiler
     * @param  $urlGenerator
     * @param string|null $baseUrl
     * @param string|null $basePath
     */
    public function __construct(
        DebugBar $webprofiler,
        string $baseUrl = null,
        string $basePath = null
    ) {
        parent::__construct($webprofiler, $baseUrl, $basePath);

        $this->webprofiler = $webprofiler;

        $this->cssFiles['narrowspark'] = __DIR__ . '/Resources/narrowspark-debugbar.css';
        $this->cssFiles['fontawesome'] = __DIR__ . '/Resources/narrowspark-debugbar.css';
        $this->jsVendors['jquery'] = __DIR__ . '/Resources/jquery/jquery-3.1.1.min.js';
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

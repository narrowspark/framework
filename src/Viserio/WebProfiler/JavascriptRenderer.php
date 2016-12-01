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

    protected $urlGenerator;

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

        // $this->urlGenerator = $webprofiler->getUrlGenerator();
        $this->cssFiles['narrowspark'] = __DIR__ . '/Resources/narrowspark-debugbar.css';
        $this->cssVendors['fontawesome'] = __DIR__ . '/Resources/vendor/font-awesome/style.css';
    }

    /**
     * {@inheritdoc}
     */
    // public function renderHead()
    // {
    //     $cssRoute = $this->urlGenerator->route('debugbar.assets.css', [
    //         'v' => $this->getModifiedTime('css'),
    //     ]);

    //     $jsRoute = $this->urlGenerator->route('debugbar.assets.js', [
    //         'v' => $this->getModifiedTime('js'),
    //     ]);

    //     $cssRoute = preg_replace('/\Ahttps?:/', '', $cssRoute);
    //     $jsRoute  = preg_replace('/\Ahttps?:/', '', $jsRoute);

    //     $html  = "<link rel='stylesheet' type='text/css' property='stylesheet' href='{$cssRoute}'>";
    //     $html .= "<script type='text/javascript' src='{$jsRoute}'></script>";

    //     if ($this->isJqueryNoConflictEnabled()) {
    //         $html .= '<script type="text/javascript">jQuery.noConflict(true);</script>' . "\n";
    //     }

    //     return $html;
    // }

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
}

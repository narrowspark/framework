<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer as BaseJavascriptRenderer;

class JavascriptRenderer extends BaseJavascriptRenderer
{
    // Use XHR handler by default, instead of jQuery
    protected $ajaxHandlerBindToJquery = false;
    protected $ajaxHandlerBindToXHR = true;

    /**
     * Create a new file javascript renderer instance.
     *
     * @param \DebugBar\DebugBar $debugBar
     * @param string|null        $baseUrl
     * @param string|null        $basePath
     */
    public function __construct(DebugBar $debugBar, string $baseUrl = null, string $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);

        $this->cssFiles['narrowspark'] = __DIR__ . '/Resources/narrowspark-debugbar.css';
        $this->cssVendors['fontawesome'] = __DIR__ . '/Resources/vendor/font-awesome/style.css';
    }

    /**
     * {@inheritdoc}
     */
    public function renderHead()
    {
        $cssRoute = route('debugbar.assets.css', [
            'v' => $this->getModifiedTime('css')
        ]);

        $jsRoute = route('debugbar.assets.js', [
            'v' => $this->getModifiedTime('js')
        ]);

        $cssRoute = preg_replace('/\Ahttps?:/', '', $cssRoute);
        $jsRoute  = preg_replace('/\Ahttps?:/', '', $jsRoute);

        $html  = "<link rel='stylesheet' type='text/css' property='stylesheet' href='{$cssRoute}'>";
        $html .= "<script type='text/javascript' src='{$jsRoute}'></script>";

        if ($this->isJqueryNoConflictEnabled()) {
            $html .= '<script type="text/javascript">jQuery.noConflict(true);</script>' . "\n";
        }

        return $html;
    }

    /**
     * Get the last modified time of any assets.
     *
     * @param string $type 'js' or 'css'
     * @return int
     */
    protected function getModifiedTime($type)
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

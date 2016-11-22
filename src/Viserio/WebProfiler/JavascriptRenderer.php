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
}

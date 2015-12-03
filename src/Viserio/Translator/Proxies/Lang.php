<?php
namespace Viserio\Translator\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Lang.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1
 */
class Lang extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'translator';
    }

    public function get($orig, $language = false, $replacements = null)
    {
        return self::$container['translator']->getTranslation($orig, $language, $replacements);
    }
}

<?php
namespace Viserio\Translator\Proxies;

use Viserio\Support\StaticalProxyManager;

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

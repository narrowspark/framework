<?php
namespace Viserio\Translator\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Lang extends StaticalProxy
{
    public function get($orig, $language = false, $replacements = null)
    {
        return self::$container['translator']->getTranslation($orig, $language, $replacements);
    }

    protected static function getFacadeAccessor()
    {
        return 'translator';
    }
}

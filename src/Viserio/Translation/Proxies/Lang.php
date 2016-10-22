<?php
declare(strict_types=1);
namespace Viserio\Translation\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Lang extends StaticalProxy
{
    public function get($orig, $language = false, $replacements = null)
    {
        return self::$container['translator']->getTranslation($orig, $language, $replacements);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'translator';
    }
}

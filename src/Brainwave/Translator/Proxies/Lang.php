<?php

namespace Brainwave\Translator\Proxies;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Support\StaticalProxyManager;

/**
 * Lang.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1-dev
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

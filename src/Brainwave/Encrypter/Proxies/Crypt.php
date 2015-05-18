<?php

namespace Brainwave\Crypter\Proxies;

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
 * @version     0.9.8-dev
 */

use Brainwave\Support\StaticalProxyManager;

/**
 * Crypt.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Crypt extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'encrypter';
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class App extends StaticalProxy
{
    public static function make($key)
    {
        return self::$container[$key];
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return self::$container;
    }
}

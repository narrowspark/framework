<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Fixture;

use Viserio\Component\StaticalProxy\StaticalProxy;

class StaticalProxyStub extends StaticalProxy
{
    /**
     * {@inheritdoc}
     */
    public static function getInstanceIdentifier()
    {
        return 'baz';
    }

    public static function getResolvedInstance()
    {
        return static::$resolvedInstance;
    }

    public function arg($arg)
    {
        return $arg;
    }

    public function oneArg($arg)
    {
        return $arg;
    }

    public function twoArg($arg, $arga)
    {
        return $arg + $arga;
    }

    public function threeArg($arg, $arga, $argb)
    {
        return $arg + $arga + $argb;
    }

    public function fourArg($arg, $arga, $argb, $argc)
    {
        return $arg + $arga + $argb + $argc;
    }

    public function moreArg(
        $arg,
        $arga,
        $argb,
        $argc,
        $argd
    ) {
        return $arg + $arga + $argb + $argc + $argd;
    }
}

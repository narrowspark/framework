<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Proxy;

use Viserio\Component\Parsers\Dumper as DumperClass;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Dumper extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return DumperClass::class;
    }
}

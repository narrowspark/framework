<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Proxies;

use Viserio\Component\Parsers\Dumper as BaseDumper;
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
        return BaseDumper::class;
    }
}

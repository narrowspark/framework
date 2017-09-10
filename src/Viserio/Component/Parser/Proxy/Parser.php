<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Proxy;

use Viserio\Component\Parser\Parser as ParserClass;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Parser extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return ParserClass::class;
    }
}

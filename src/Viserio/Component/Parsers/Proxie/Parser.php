<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Proxie;

use Viserio\Component\Parsers\Parser as BaseParser;
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
        return BaseParser::class;
    }
}

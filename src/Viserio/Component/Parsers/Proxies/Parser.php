<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Proxies;

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
        return 'parser';
    }
}

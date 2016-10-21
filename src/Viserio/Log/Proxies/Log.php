<?php
declare(strict_types=1);
namespace Viserio\Log\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Log extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'logger';
    }
}

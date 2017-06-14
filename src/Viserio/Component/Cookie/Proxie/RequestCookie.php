<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Proxie;

use Viserio\Component\StaticalProxy\StaticalProxy;

class RequestCookie extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'request-cookie';
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Http\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Response extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'response';
    }
}

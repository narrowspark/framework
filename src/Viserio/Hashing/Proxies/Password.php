<?php
declare(strict_types=1);
namespace Viserio\Hashing\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Password extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'password';
    }
}

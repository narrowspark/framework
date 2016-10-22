<?php
declare(strict_types=1);
namespace Viserio\Validation\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Sanitizer extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'validator';
    }
}

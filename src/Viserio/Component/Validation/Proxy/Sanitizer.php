<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Proxy;

use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\Validation\Sanitizer as SanitizerClass;

class Sanitizer extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return SanitizerClass::class;
    }
}

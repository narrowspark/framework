<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Proxie;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Sanitizer extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'sanitizer';
    }
}
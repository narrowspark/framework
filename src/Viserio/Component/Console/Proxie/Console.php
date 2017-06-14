<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Proxie;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Console extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'console';
    }
}

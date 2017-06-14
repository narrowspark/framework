<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Proxie;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Queue extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'queue';
    }
}

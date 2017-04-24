<?php
declare(strict_types=1);
namespace Viserio\Component\Profileroxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Profiler extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'profiler';
    }
}

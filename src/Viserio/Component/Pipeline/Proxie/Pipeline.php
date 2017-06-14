<?php
declare(strict_types=1);
namespace Viserio\Component\Pipeline\Proxie;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Pipeline extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'pipeline';
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Proxie;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Log extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'logger';
    }
}

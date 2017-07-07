<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Proxy;

use Viserio\Component\Queue\QueueManager;
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
        return QueueManager::class;
    }
}

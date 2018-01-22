<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Viserio\Component\Support\AbstractConnectionManager;

class QueueManager extends AbstractConnectionManager
{
    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'queue';
    }
}

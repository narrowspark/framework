<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Viserio\Component\Support\AbstractConnectionManager;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;

class QueueManager extends AbstractConnectionManager implements ProvidesDefaultOptionsContract
{

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'default'   => 'array',
            'namespace' => false,
            'key'       => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'queue';
    }
}

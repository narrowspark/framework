<?php
declare(strict_types=1);
namespace Viserio\Component\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Component\Support\AbstractManager;

class LogManager extends AbstractManager implements LoggerInterface
{
    use LoggerTrait;
    use ParseLevelTrait;

    /**
     * Get a log channel instance.
     *
     * @param string|null  $channel
     *
     * @return mixed
     */
    public function channel(?string $channel = null)
    {
        return $this->getDriver($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $this->getDriver()->log($level, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'logging';
    }
}

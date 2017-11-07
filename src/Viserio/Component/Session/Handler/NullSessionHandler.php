<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

/**
 * Can be used in unit testing or in a situations where persisted sessions are not desired.
 */
class NullSessionHandler extends AbstractSessionHandler
{
    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $data): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId): bool
    {
        return true;
    }
}

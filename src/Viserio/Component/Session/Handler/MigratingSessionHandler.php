<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 * Migrating session handler for migrating from one handler to another. It reads
 * from the current handler and writes both the current and new ones.
 *
 * It ignores errors from the new handler.
 *
 * @author Ross Motley <ross.motley@amara.com>
 * @author Oliver Radwell <oliver.radwell@amara.com>
 */
class MigratingSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    /**
     * A session handler instance.
     *
     * @var \SessionHandlerInterface|\SessionUpdateTimestampHandlerInterface|StrictSessionHandler
     */
    private $currentHandler;

    /**
     * A session handler instance.
     *
     * @var \SessionHandlerInterface|\SessionUpdateTimestampHandlerInterface|StrictSessionHandler
     */
    private $writeOnlyHandler;

    /**
     * Create a new MigratingSessionHandler instance.
     *
     * @param \SessionHandlerInterface $currentHandler
     * @param \SessionHandlerInterface $writeOnlyHandler
     */
    public function __construct(SessionHandlerInterface $currentHandler, SessionHandlerInterface $writeOnlyHandler)
    {
        if (! $currentHandler instanceof SessionUpdateTimestampHandlerInterface) {
            $currentHandler = new StrictSessionHandler($currentHandler);
        }

        if (! $writeOnlyHandler instanceof SessionUpdateTimestampHandlerInterface) {
            $writeOnlyHandler = new StrictSessionHandler($writeOnlyHandler);
        }

        $this->currentHandler   = $currentHandler;
        $this->writeOnlyHandler = $writeOnlyHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $result = $this->currentHandler->close();

        $this->writeOnlyHandler->close();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $result = $this->currentHandler->destroy($sessionId);

        $this->writeOnlyHandler->destroy($sessionId);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        $result = $this->currentHandler->gc($maxlifetime);

        $this->writeOnlyHandler->gc($maxlifetime);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        $result = $this->currentHandler->open($savePath, $sessionName);

        $this->writeOnlyHandler->open($savePath, $sessionName);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        // No reading from new handler until switch-over
        return $this->currentHandler->read($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $sessionData)
    {
        $result = $this->currentHandler->write($sessionId, $sessionData);

        $this->writeOnlyHandler->write($sessionId, $sessionData);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        // No reading from new handler until switch-over
        return $this->currentHandler->validateId($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        $result = $this->currentHandler->updateTimestamp($sessionId, $sessionData);

        $this->writeOnlyHandler->updateTimestamp($sessionId, $sessionData);

        return $result;
    }
}

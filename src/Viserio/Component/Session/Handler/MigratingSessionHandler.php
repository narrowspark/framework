<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
     * @var \SessionHandlerInterface & \SessionUpdateTimestampHandlerInterface
     */
    private $currentHandler;

    /**
     * A session handler instance.
     *
     * @var \SessionHandlerInterface & \SessionUpdateTimestampHandlerInterface
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

        $this->currentHandler = $currentHandler;
        $this->writeOnlyHandler = $writeOnlyHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        $result = $this->currentHandler->close();

        $this->writeOnlyHandler->close();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $result = $this->currentHandler->destroy($sessionId);

        $this->writeOnlyHandler->destroy($sessionId);

        return $result;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     *
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        $result = $this->currentHandler->gc($maxlifetime);

        $this->writeOnlyHandler->gc($maxlifetime);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        $result = $this->currentHandler->open($savePath, $sessionName);

        $this->writeOnlyHandler->open($savePath, $sessionName);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId): string
    {
        // No reading from new handler until switch-over
        return $this->currentHandler->read($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $sessionData): bool
    {
        $result = $this->currentHandler->write($sessionId, $sessionData);

        $this->writeOnlyHandler->write($sessionId, $sessionData);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId): bool
    {
        // No reading from new handler until switch-over
        return $this->currentHandler->validateId($sessionId);
    }

    /**
     * Update timestamp of a session.
     *
     * @param string $sessionId   The session id
     * @param string $sessionData
     *
     * @return bool
     */
    public function updateTimestamp($sessionId, $sessionData): bool
    {
        $result = $this->currentHandler->updateTimestamp($sessionId, $sessionData);

        $this->writeOnlyHandler->updateTimestamp($sessionId, $sessionData);

        return $result;
    }
}

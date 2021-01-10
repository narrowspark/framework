<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Session\Handler;

use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;
use Viserio\Contract\Session\Exception\LogicException;

/**
 * Adds basic `SessionUpdateTimestampHandlerInterface` behaviors to another `SessionHandlerInterface`.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class StrictSessionHandler extends AbstractSessionHandler
{
    /** @var SessionHandlerInterface */
    private $handler;

    /** @var bool */
    private $doDestroy;

    /**
     * StrictSessionHandler constructor.
     *
     * @throws \Viserio\Contract\Session\Exception\LogicException
     */
    public function __construct(SessionHandlerInterface $handler)
    {
        if ($handler instanceof SessionUpdateTimestampHandlerInterface) {
            throw new LogicException(\sprintf('[%s] is already an instance of "SessionUpdateTimestampHandlerInterface", you cannot wrap it with [%s].', \get_class($handler), self::class));
        }

        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        parent::open($savePath, $sessionName);

        return $this->handler->open($savePath, $sessionName);
    }

    /**
     * Update timestamp of a session.
     *
     * @param string $sessionId   The session id
     * @param string $sessionData
     */
    public function updateTimestamp($sessionId, $sessionData): bool
    {
        return $this->write($sessionId, $sessionData);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $this->doDestroy = true;

        $destroyed = parent::destroy($sessionId);

        return $this->doDestroy === true ? $this->doDestroy($sessionId) : $destroyed;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return $this->handler->close();
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime): bool
    {
        return $this->handler->gc($maxlifetime);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId): bool
    {
        $this->doDestroy = false;

        return $this->handler->destroy($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId): string
    {
        return $this->handler->read($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $data): bool
    {
        return $this->handler->write($sessionId, $data);
    }
}

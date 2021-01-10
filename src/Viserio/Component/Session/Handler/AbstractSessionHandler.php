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

abstract class AbstractSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    /** @var null|string */
    private $sessionName;

    /** @var null|string */
    private $prefetchId;

    /** @var null|string */
    private $prefetchData;

    /** @var null|string */
    private $newSessionId;

    /** @var null|string */
    private $igbinaryEmptyData;

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        $this->sessionName = $sessionName;

        return true;
    }

    /**
     * Validate session id.
     *
     * @param string $sessionId The session id
     *
     * @return bool note this value is returned internally to PHP for processing
     */
    public function validateId($sessionId): bool
    {
        $this->prefetchData = $this->read($sessionId);
        $this->prefetchId = $sessionId;

        return $this->prefetchData !== '';
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId): string
    {
        if ($this->prefetchId !== null) {
            $prefetchId = $this->prefetchId;
            $prefetchData = $this->prefetchData;

            $this->prefetchId = $this->prefetchData = null;

            if ($prefetchId === $sessionId || '' === $prefetchData) {
                $this->newSessionId = '' === $prefetchData ? $sessionId : null;

                return $prefetchData;
            }
        }

        $data = $this->doRead($sessionId);
        $this->newSessionId = '' === $data ? $sessionId : null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        if ($this->igbinaryEmptyData === null) {
            /** @see igbinary/igbinary/issues/146 */
            $this->igbinaryEmptyData = \function_exists('igbinary_serialize') ? \igbinary_serialize([]) : '';
        }

        if ($data === '' || $this->igbinaryEmptyData === $data) {
            return $this->destroy($sessionId);
        }

        $this->newSessionId = null;

        return $this->doWrite($sessionId, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        return $this->newSessionId === $sessionId || $this->doDestroy($sessionId);
    }

    abstract protected function doRead(string $sessionId): string;

    abstract protected function doWrite(string $sessionId, string $data): bool;

    abstract protected function doDestroy(string $sessionId): bool;
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

abstract class AbstractSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    /**
     * @var string|null
     */
    private $sessionName;

    /**
     * @var string|null
     */
    private $prefetchId;

    /**
     * @var string|null
     */
    private $prefetchData;

    /**
     * @var string|null
     */
    private $newSessionId;

    /**
     * @var string|null
     */
    private $igbinaryEmptyData;

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        $this->sessionName = $sessionName;

        return true;
    }

    /**
     * @param string $sessionId
     *
     * @return string
     */
    abstract protected function doRead($sessionId): string;

    /**
     * @param string $sessionId
     * @param string $data
     *
     * @return bool
     */
    abstract protected function doWrite($sessionId, $data): bool;

    /**
     * @param string $sessionId
     *
     * @return bool
     */
    abstract protected function doDestroy($sessionId): bool;

    /**
     * {@inheritdoc}
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
            $prefetchId   = $this->prefetchId;
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
            // see igbinary/igbinary/issues/146
            $this->igbinaryEmptyData = \function_exists('igbinary_serialize') ? igbinary_serialize([]) : '';
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
}
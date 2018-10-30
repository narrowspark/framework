<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;
use Viserio\Component\Contract\Session\Exception\LogicException;

/**
 * Adds basic `SessionUpdateTimestampHandlerInterface` behaviors to another `SessionHandlerInterface`.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class StrictSessionHandler extends AbstractSessionHandler
{
    /**
     * @var \SessionHandlerInterface
     */
    private $handler;

    /**
     * @var bool
     */
    private $doDestroy;

    /**
     * StrictSessionHandler constructor.
     *
     * @param \SessionHandlerInterface $handler
     *
     * @throws \Viserio\Component\Contract\Session\Exception\LogicException
     */
    public function __construct(SessionHandlerInterface $handler)
    {
        if ($handler instanceof SessionUpdateTimestampHandlerInterface) {
            throw new LogicException(
                \sprintf(
                '[%s] is already an instance of "SessionUpdateTimestampHandlerInterface", you cannot wrap it with [%s].',
                \get_class($handler),
                self::class
            )
            );
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
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data): bool
    {
        return $this->write($sessionId, $data);
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
     * {@inheritdoc}
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

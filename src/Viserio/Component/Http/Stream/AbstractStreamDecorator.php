<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use BadMethodCallException;
use Psr\Http\Message\StreamInterface;
use Throwable;
use UnexpectedValueException;
use Viserio\Component\Http\Util;

abstract class AbstractStreamDecorator implements StreamInterface
{
    /**
     * Create a new stream instance.
     *
     * @param \Psr\Http\Message\StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Magic method used to create a new stream if streams are not added in
     * the constructor of a decorator (e.g., LazyOpenStream).
     *
     * @param string $name Name of the property (allows "stream" only).
     *
     * @throws \UnexpectedValueException
     *
     * @return StreamInterface
     */
    public function __get($name)
    {
        if ($name === 'stream') {
            $this->stream = $this->createStream();

            return $this->stream;
        }

        throw new UnexpectedValueException(\sprintf('%s not found on class.', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (Throwable $e) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            \trigger_error('StreamDecorator::__toString exception: '
                . (string) $e, E_USER_ERROR);

            return '';
        }
    }

    /**
     * Allow decorators to implement custom methods.
     *
     * @param string $method Missing method name
     * @param array  $args   Method arguments
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        $result = \call_user_func_array([$this->stream, $method], $args);

        // Always return the wrapped object if the result is a return $this
        return $result === $this->stream ? $this : $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return Util::copyToString($this);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->stream->close();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return $this->stream->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return $this->stream->tell();
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return $this->stream->isWritable();
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        return $this->stream->read($length);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        return $this->stream->write($string);
    }

    /**
     * Implement in subclasses to dynamically create streams when requested.
     *
     * @throws \BadMethodCallException
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        throw new BadMethodCallException('Not implemented.');
    }
}

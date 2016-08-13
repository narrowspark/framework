<?php
declare(strict_types=1);
namespace Viserio\Http\Stream;

use BadMethodCallException;
use Exception;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;
use Viserio\Http\Util;

abstract class AbstractStreamDecorator implements StreamInterface
{
    /**
     * Stream instance.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;

    /**
     * @param StreamInterface $stream Stream to decorate
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
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
        } catch (Exception $e) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            trigger_error('StreamDecorator::__toString exception: '
                . (string) $e, E_USER_ERROR);

            return '';
        }
    }

    /**
     * Allow decorators to implement custom methods
     *
     * @param string $method Missing method name
     * @param array  $args   Method arguments
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        $result = call_user_func_array([$this->stream, $method], $args);

        // Always return the wrapped object if the result is a return $this
        return $result === $this->stream ? $this : $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return Util::copyToString($this);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
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
    public function getSize()
    {
        return $this->stream->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return $this->stream->eof();
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return $this->stream->tell();
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return $this->stream->isReadable();
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->stream->seek($offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        return $this->stream->read($length);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        return $this->stream->write($string);
    }

    /**
     * Implement in subclasses to dynamically create streams when requested.
     *
     * @throws \BadMethodCallException
     *
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        throw new BadMethodCallException('Not implemented');
    }
}

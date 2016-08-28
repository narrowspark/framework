<?php
declare(strict_types=1);
namespace Viserio\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Throwable;
use UnexpectedValueException;
use Viserio\Http\Stream;
use Viserio\Http\Util;

class LazyOpenStream implements StreamInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $mode;

    /**
     * @param string $filename File to lazily open
     * @param string $mode     fopen mode to use when opening the stream
     */
    public function __construct(string $filename, string $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
    }

    /**
     * Magic method used to create a new stream if streams are not added in
     * the constructor of LazyOpenStream.
     *
     * @param string $name Name of the property (allows "stream" only).
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function __get($name)
    {
        if ($name == 'stream') {
            $this->stream = $this->createStream();

            return $this->stream;
        }

        throw new UnexpectedValueException(sprintf('%s not found on class', $name));
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
            trigger_error('StreamDecorator::__toString exception: '
                . (string) $e, E_USER_ERROR);

            return '';
        }
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
     * Creates the underlying stream lazily when required.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new Stream(Util::tryFopen($this->filename, $this->mode));
    }
}

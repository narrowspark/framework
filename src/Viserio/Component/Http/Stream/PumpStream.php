<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Viserio\Component\Http\Util;

class PumpStream implements StreamInterface
{
    /**
     * The size of the stream if known.
     *
     * @var null|int
     */
    protected $size;
    /**
     * Source of the stream data.
     *
     * @var callable|null
     */
    private $source;

    /**
     *@var int
     */
    private $tellPos = 0;

    /**
     * Stream metadata.
     *
     * @var array
     */
    private $metadata;

    /**
     * Buffer stream instance.
     *
     * @var \Viserio\Component\Http\Stream\BufferStream
     */
    private $buffer;

    /**
     * Create a new pump stream instance.
     *
     * @param callable $source  Source of the stream data. The callable MAY
     *                          accept an integer argument used to control the
     *                          amount of data to return. The callable MUST
     *                          return a string when called, or false on error
     *                          or EOF.
     * @param array    $options Stream options:
     *                          - metadata: Hash of metadata to use with stream.
     *                          - size: Size of the stream, if known.
     */
    public function __construct(callable $source, array $options = [])
    {
        $this->source   = $source;
        $this->size     = isset($options['size']) ? $options['size'] : null;
        $this->metadata = isset($options['metadata']) ? $options['metadata'] : [];
        $this->buffer   = new BufferStream();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            return Util::copyToString($this);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $this->tellPos = 0;
        $this->source  = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return $this->tellPos;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return ! $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return false;
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
        throw new RuntimeException('Cannot seek a PumpStream');
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        throw new RuntimeException('Cannot write to a PumpStream');
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $data    = $this->buffer->read($length);
        $readLen = mb_strlen($data);
        $this->tellPos += $readLen;
        $remaining = $length - $readLen;

        if ($remaining) {
            $this->pump($remaining);
            $data .= $this->buffer->read($remaining);
            $this->tellPos += mb_strlen($data) - $readLen;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $result = '';
        while (! $this->eof()) {
            $result .= $this->read(1000000);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (! $key) {
            return $this->metadata;
        }

        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }

    /**
     * @param int $length
     *
     * @return void|null
     */
    private function pump($length)
    {
        if ($this->source) {
            do {
                $data = call_user_func($this->source, $length);

                if ($data === false || $data === null) {
                    $this->source = null;

                    return;
                }

                $this->buffer->write($data);

                $length -= mb_strlen($data);
            } while ($length > 0);
        }
    }
}

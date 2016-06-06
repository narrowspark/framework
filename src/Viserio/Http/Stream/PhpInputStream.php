<?php
namespace Viserio\Http\Stream;

use Viserio\Http\Stream;

class PhpInputStream extends AbstractStreamDecorator
{
    /**
     * @var string
     */
    private $cache = '';

    /**
     * @var bool
     */
    private $reachedEof = false;

    /**
     * @param string|resource $stream
     */
    public function __construct($stream = 'php://input')
    {
        $this->stream = new Stream($stream, 'r');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if ($this->reachedEof) {
            return $this->cache;
        }

        $this->getContents();

        return $this->cache;
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
    public function read($length)
    {
        $content = parent::read($length);

        if ($content && ! $this->reachedEof) {
            $this->cache .= $content;
        }

        if ($this->eof()) {
            $this->reachedEof = true;
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($maxLength = -1)
    {
        if ($this->reachedEof) {
            return $this->cache;
        }

        $contents = stream_get_contents($this->resource, $maxLength);
        $this->cache .= $contents;

        if ($maxLength === -1 || $this->eof()) {
            $this->reachedEof = true;
        }

        return $contents;
    }
}

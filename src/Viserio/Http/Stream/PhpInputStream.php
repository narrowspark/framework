<?php

declare(strict_types=1);
namespace Viserio\Http\Stream;

use Viserio\Http\{
    Stream,
    Util
};

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
        $stream = Util::tryFopen($stream, 'r');

        $this->stream = new Stream($stream);
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
    public function getContents()
    {
        if ($this->reachedEof) {
            return $this->cache;
        }

        $contents = $this->stream->getContents();
        $this->cache .= $contents;

        if ($this->eof()) {
            $this->reachedEof = true;
        }

        return $contents;
    }
}

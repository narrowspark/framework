<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Util;

class PhpInputStream extends AbstractStreamDecorator
{
    /**
     * Cached content-.
     *
     * @var string
     */
    private $cache = '';

    /**
     * True or false if eof is reached.
     *
     * @var bool
     */
    private $reachedEof = false;

    /**
     * Create a new php input stream instance.
     *
     * @param string|resource $stream
     */
    public function __construct($stream = 'php://input')
    {
        if (is_string($stream)) {
            $stream = Util::tryFopen($stream, 'r');
        }

        parent::__construct(new Stream($stream));
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

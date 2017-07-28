<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use RuntimeException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Util;

class PhpInputStream extends AbstractStreamDecorator
{
    /**
     * Stream instance.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;
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
     * @param resource|string $stream
     */
    public function __construct($stream = 'php://input')
    {
        if (\is_string($stream)) {
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

        try {
            $this->getContents();
        } catch (RuntimeException $exception) {
            return '';
        }

        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
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
    public function getContents(): string
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

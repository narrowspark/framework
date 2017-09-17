<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Util;

class LazyOpenStream extends AbstractStreamDecorator
{
    /**
     * File name which should be lazily open.
     *
     * @var string
     */
    private $filename;

    /**
     * fopen mode to use when opening the stream.
     *
     * @var string
     */
    private $mode;

    /**
     * Create a new lazy open stream instance.
     *
     * @param string $filename
     * @param string $mode
     */
    public function __construct(string $filename, string $mode)
    {
        $this->filename = $filename;
        $this->mode     = $mode;
    }

    /**
     * {@inheritdoc}
     */
    protected function createStream(): StreamInterface
    {
        return new Stream(Util::tryFopen($this->filename, $this->mode));
    }
}

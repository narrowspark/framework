<?php
declare(strict_types=1);
namespace Viserio\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Viserio\Http\Util;

class LazyOpenStream extends AbstractStreamDecorator
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
    public function __construct($filename, $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
        $this->stream = $this->createStream();
    }

    /**
     * Creates the underlying stream lazily when required.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return Util::getStream(Util::tryFopen($this->filename, $this->mode));
    }
}

<?php
namespace Viserio\Http\Stream;

use RuntimeException;

class NoSeekStream extends AbstractStreamDecorator
{
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Cannot seek a NoSeekStream');
    }

    public function isSeekable()
    {
        return false;
    }
}

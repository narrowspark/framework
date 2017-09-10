<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Viserio\Component\Contract\Http\Exception\RuntimeException;

class NoSeekStream extends AbstractStreamDecorator
{
    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new RuntimeException('Cannot seek a NoSeekStream');
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return false;
    }
}

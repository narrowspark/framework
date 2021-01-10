<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Http\Response\Traits;

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 */
trait CreateBodyTrait
{
    /**
     * Create the message body.
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if $text is neither a string or stream
     */
    private function createBody($text): StreamInterface
    {
        if ($text instanceof StreamInterface) {
            return $text;
        }

        if (! \is_string($text)) {
            throw new InvalidArgumentException(\sprintf('Invalid content [%s] provided to %s.', (\is_object($text) ? \get_class($text) : \gettype($text)), __CLASS__));
        }

        $body = new Stream('php://temp', ['mode' => 'wb+']);
        $body->write($text);
        $body->rewind();

        return $body;
    }
}

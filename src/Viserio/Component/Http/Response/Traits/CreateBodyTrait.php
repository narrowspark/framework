<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     * @param mixed $text
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if $text is neither a string or stream
     *
     * @return \Psr\Http\Message\StreamInterface
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

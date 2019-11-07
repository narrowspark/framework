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

namespace Viserio\Component\HttpFactory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = \fopen('php://memory', 'r+b');

        \fwrite($stream, $content);
        \fseek($stream, 0);

        return Util::createStreamFor($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        try {
            $resource = Util::tryFopen($filename, $mode);
        } catch (RuntimeException $exception) {
            // PSR-17 requires to throw an InvalidArgumentException for invalid modes.
            // But we do not want to validate the modes ourselves as the accepted modes depend on the OS.
            // The following error messages seem to be returned usually for strange modes.
            if ($mode === '' || \strpos($exception->getMessage(), 'failed to open stream: No error') !== false || \strpos($exception->getMessage(), 'failed to open stream: Success') !== false) {
                throw new InvalidArgumentException(\sprintf('Invalid file opening mode "%s".', $mode), 0, $exception);
            }

            throw $exception;
        }

        return Util::createStreamFor($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return Util::createStreamFor($resource);
    }
}

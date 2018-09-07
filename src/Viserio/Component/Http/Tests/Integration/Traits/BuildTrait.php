<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Integration\Traits;

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\PumpStream;
use Viserio\Component\Http\UploadedFile;
use Viserio\Component\Http\Uri;

trait BuildTrait
{
    protected function buildUri($uri): Uri
    {
        return Uri::createFromString($uri);
    }

    protected function buildStream($data): StreamInterface
    {
        if (\is_scalar($data)) {
            return $this->createStreamForScalar($data);
        }

        $type = \gettype($data);

        if ($type === 'resource') {
            return new Stream($data);
        }

        if ($type === 'object') {
            if ($data instanceof StreamInterface) {
                return $data;
            }

            if ($data instanceof \Iterator) {
                return new PumpStream(static function () use ($data) {
                    if (! $data->valid()) {
                        return false;
                    }

                    $result = $data->current();
                    $data->next();

                    return $result;
                });
            }

            if (\method_exists($data, '__toString')) {
                return $this->createStreamForScalar($data->__toString());
            }
        }

        if ($type === 'NULL') {
            return new Stream(\fopen('php://temp', 'r+b'));
        }

        if (\is_callable($data)) {
            return new PumpStream($data);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . $type);
    }

    protected function buildUploadableFile($data): UploadedFile
    {
        return new UploadedFile($data, \mb_strlen($data));
    }

    /**
     * Creates a stream for scalar types.
     *
     * @param bool|float|int|string $resource
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function createStreamForScalar($resource): StreamInterface
    {
        $stream = \fopen('php://temp', 'r+b');

        if ($resource !== '') {
            \fwrite($stream, $resource);
            \fseek($stream, 0);
        }

        return new Stream($stream);
    }
}

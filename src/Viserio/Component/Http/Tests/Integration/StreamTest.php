<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Integration;

use Http\Psr7Test\StreamIntegrationTest;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Tests\Integration\Traits\BuildTrait;

/**
 * @internal
 */
final class StreamTest extends StreamIntegrationTest
{
    use BuildTrait;

    /**
     * {@inheritdoc}
     */
    public function createStream($data): StreamInterface
    {
        return $this->streamFor($data);
    }

    /**
     * Create a new stream based on the input type.
     *
     * @param null|bool|callable|float|int|\Iterator|resource|StreamInterface|string $resource Entity body data
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if the $resource arg is not valid
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function streamFor($resource): StreamInterface
    {
        return new Stream($resource);
    }
}

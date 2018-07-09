<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Tests\Traits;

use Interop\Http\Factory\StreamFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\HttpFactory\Traits\StreamFactoryAwareTrait;

/**
 * @internal
 */
final class StreamFactoryAwareTraitTest extends MockeryTestCase
{
    use StreamFactoryAwareTrait;

    public function testSetAndGetStreamFactory(): void
    {
        $this->setStreamFactory($this->mock(StreamFactoryInterface::class));

        static::assertInstanceOf(StreamFactoryInterface::class, $this->streamFactory);
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\StreamFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\StreamFactoryAwareTrait;

class StreamFactoryAwareTraitTest extends MockeryTestCase
{
    use StreamFactoryAwareTrait;

    public function testSetAndGetStreamFactory(): void
    {
        $this->setStreamFactory($this->mock(StreamFactoryInterface::class));

        self::assertInstanceOf(StreamFactoryInterface::class, $this->streamFactory);
    }
}

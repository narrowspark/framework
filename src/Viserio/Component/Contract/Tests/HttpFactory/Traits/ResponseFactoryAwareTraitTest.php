<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;

/**
 * @internal
 */
final class ResponseFactoryAwareTraitTest extends MockeryTestCase
{
    use ResponseFactoryAwareTrait;

    public function testSetAndGetResponseFactory(): void
    {
        $this->setResponseFactory($this->mock(ResponseFactoryInterface::class));

        static::assertInstanceOf(ResponseFactoryInterface::class, $this->responseFactory);
    }
}

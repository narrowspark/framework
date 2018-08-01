<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Viserio\Component\Contract\HttpFactory\Traits\RequestFactoryAwareTrait;

/**
 * @internal
 */
final class RequestFactoryAwareTraitTest extends MockeryTestCase
{
    use RequestFactoryAwareTrait;

    public function testSetAndGetRequestFactory(): void
    {
        $this->setRequestFactory($this->mock(RequestFactoryInterface::class));

        static::assertInstanceOf(RequestFactoryInterface::class, $this->requestFactory);
    }
}

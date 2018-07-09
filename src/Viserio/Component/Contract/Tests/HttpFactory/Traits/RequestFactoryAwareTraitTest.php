<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Tests\Traits;

use Interop\Http\Factory\RequestFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
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

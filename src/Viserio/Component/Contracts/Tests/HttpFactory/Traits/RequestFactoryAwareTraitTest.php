<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\RequestFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\RequestFactoryAwareTrait;

class RequestFactoryAwareTraitTest extends MockeryTestCase
{
    use RequestFactoryAwareTrait;

    public function testSetAndGetRequestFactory(): void
    {
        $this->setRequestFactory($this->mock(RequestFactoryInterface::class));

        self::assertInstanceOf(RequestFactoryInterface::class, $this->serverRequestFactory);
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\ServerRequestFactoryAwareTrait;

class ServerRequestFactoryAwareTraitTest extends MockeryTestCase
{
    use ServerRequestFactoryAwareTrait;

    public function testSetAndGetServerRequestFactory(): void
    {
        $this->setServerRequestFactory($this->mock(ServerRequestFactoryInterface::class));

        self::assertInstanceOf(ServerRequestFactoryInterface::class, $this->serverRequest);
    }
}

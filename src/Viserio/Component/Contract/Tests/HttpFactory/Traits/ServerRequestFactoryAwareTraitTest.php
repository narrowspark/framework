<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Tests\Traits;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\HttpFactory\Traits\ServerRequestFactoryAwareTrait;

/**
 * @internal
 */
final class ServerRequestFactoryAwareTraitTest extends MockeryTestCase
{
    use ServerRequestFactoryAwareTrait;

    public function testSetAndGetServerRequestFactory(): void
    {
        $this->setServerRequestFactory($this->mock(ServerRequestFactoryInterface::class));

        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $this->serverRequestFactory);
    }
}

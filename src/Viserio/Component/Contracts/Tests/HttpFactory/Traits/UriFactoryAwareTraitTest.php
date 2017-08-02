<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\UriFactoryAwareTrait;

class UriFactoryAwareTraitTest extends MockeryTestCase
{
    use UriFactoryAwareTrait;

    public function testSetAndGetUriFactory(): void
    {
        $this->setUriFactory($this->mock(UriFactoryInterface::class));

        self::assertInstanceOf(UriFactoryInterface::class, $this->uriFactory);
    }
}

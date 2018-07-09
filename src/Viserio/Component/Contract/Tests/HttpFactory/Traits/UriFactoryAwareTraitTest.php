<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Tests\Traits;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\HttpFactory\Traits\UriFactoryAwareTrait;

/**
 * @internal
 */
final class UriFactoryAwareTraitTest extends MockeryTestCase
{
    use UriFactoryAwareTrait;

    public function testSetAndGetUriFactory(): void
    {
        $this->setUriFactory($this->mock(UriFactoryInterface::class));

        static::assertInstanceOf(UriFactoryInterface::class, $this->uriFactory);
    }
}

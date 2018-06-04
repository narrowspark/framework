<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parser\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;
use Viserio\Component\Contract\Parser\Traits\ParserAwareTrait;

/**
 * @internal
 */
final class LoaderAwareTraitTest extends MockeryTestCase
{
    use ParserAwareTrait;

    public function testGetAndSetLoader(): void
    {
        $this->setLoader($this->mock(LoaderContract::class));

        $this->assertInstanceOf(LoaderContract::class, $this->getLoader());
    }

    public function testGetLoaderThrowExceptionIfLoaderIsNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Loader is not set up.');

        $this->getLoader();
    }
}

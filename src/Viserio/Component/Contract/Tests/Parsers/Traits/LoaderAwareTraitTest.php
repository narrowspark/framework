<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parser\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;
use Viserio\Component\Contract\Parser\Traits\ParserAwareTrait;

class LoaderAwareTraitTest extends MockeryTestCase
{
    use ParserAwareTrait;

    public function testGetAndSetLoader(): void
    {
        $this->setLoader($this->mock(LoaderContract::class));

        self::assertInstanceOf(LoaderContract::class, $this->getLoader());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Loader is not set up.
     */
    public function testGetLoaderThrowExceptionIfLoaderIsNotSet(): void
    {
        $this->getLoader();
    }
}

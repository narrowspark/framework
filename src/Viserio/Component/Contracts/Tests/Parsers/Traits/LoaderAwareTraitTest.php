<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Parsers\Traits\ParserAwareTrait;

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

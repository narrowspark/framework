<?php
declare(strict_types=1);
namespace Viserio\Contracts\Parsers\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Contracts\Parsers\Traits\LoaderAwareTrait;

class LoaderAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use LoaderAwareTrait;

    public function testGetAndSetLoader()
    {
        $this->setLoader($this->mock(LoaderContract::class));

        self::assertInstanceOf(LoaderContract::class, $this->getLoader());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Loader is not set up.
     */
    public function testGetLoaderThrowExceptionIfLoaderIsNotSet()
    {
        $this->getLoader();
    }
}

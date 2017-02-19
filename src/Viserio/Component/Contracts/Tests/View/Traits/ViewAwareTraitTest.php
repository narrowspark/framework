<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\View\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\View\Factory as ViewFactoryContract;
use Viserio\Component\Contracts\View\Traits\ViewAwareTrait;

class ViewAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use ViewAwareTrait;

    public function testGetAndSetViewFactory()
    {
        $this->setViewFactory($this->mock(ViewFactoryContract::class));

        self::assertInstanceOf(ViewFactoryContract::class, $this->getViewFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage View factory is not set up.
     */
    public function testGetViewFactoryThrowExceptionIfViewFactoryIsNotSet()
    {
        $this->getViewFactory();
    }
}

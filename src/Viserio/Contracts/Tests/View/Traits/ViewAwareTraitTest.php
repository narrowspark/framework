<?php
declare(strict_types=1);
namespace Viserio\Contracts\View\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\View\Factory as ViewFactoryContract;
use Viserio\Contracts\View\Traits\ViewAwareTrait;

class ViewAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use ViewAwareTrait;

    public function testGetAndSetViewFactory()
    {
        $this->setViewFactory($this->mock(ViewFactoryContract::class));

        $this->assertInstanceOf(ViewFactoryContract::class, $this->getViewFactory());
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

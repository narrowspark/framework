<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\View\Tests\Traits;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\View\Factory as ViewFactoryContract;
use Viserio\Component\Contract\View\Traits\ViewAwareTrait;

class ViewAwareTraitTest extends MockeryTestCase
{
    use ViewAwareTrait;

    public function testGetAndSetViewFactory(): void
    {
        $this->setViewFactory($this->mock(ViewFactoryContract::class));

        self::assertInstanceOf(ViewFactoryContract::class, $this->viewFactory);
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Tests\Traits;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;

class ContainerAwareTraitTest extends TestCase
{
    use ContainerAwareTrait;

    public function testGetAndSetContainer(): void
    {
        $this->setContainer(new ArrayContainer());

        self::assertInstanceOf(ContainerInterface::class, $this->container);
    }
}

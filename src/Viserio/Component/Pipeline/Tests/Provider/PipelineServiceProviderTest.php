<?php
declare(strict_types=1);
namespace Viserio\Component\Pipeline\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Pipeline\Provider\PipelineServiceProvider;

class PipelineServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new PipelineServiceProvider());

        self::assertInstanceOf(Pipeline::class, $container->get(Pipeline::class));
        self::assertInstanceOf(Pipeline::class, $container->get(PipelineContract::class));
        self::assertInstanceOf(Pipeline::class, $container->get('pipeline'));
    }
}

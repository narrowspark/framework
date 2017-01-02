<?php
declare(strict_types=1);
namespace Viserio\Pipeline\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Container\Container;
use Viserio\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Pipeline\Pipeline;
use Viserio\Pipeline\Providers\PipelineServiceProvider;

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

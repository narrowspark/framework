<?php
declare(strict_types=1);
namespace Viserio\Pipeline\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Pipeline\Pipeline;
use Viserio\Pipeline\Providers\PipelineServiceProvider;

class PipelineServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new PipelineServiceProvider());

        $this->assertInstanceOf(Pipeline::class, $container->get(Pipeline::class));
        $this->assertInstanceOf(Pipeline::class, $container->get(PipelineContract::class));
        $this->assertInstanceOf(Pipeline::class, $container->get('pipeline'));
    }
}

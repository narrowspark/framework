<?php
declare(strict_types=1);
namespace Viserio\Foundation\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Foundation\Providers\FoundationDataCollectorsServiceProvider;

class FoundationDataCollectorsServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new FoundationDataCollectorsServiceProvider());
    }
}

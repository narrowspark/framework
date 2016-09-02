<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Providers\FilesServiceProvider;

class FilesServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());

        $this->assertInstanceOf(Filesystem::class, $container->get(Filesystem::class));
        $this->assertInstanceOf(Filesystem::class, $container->get('files'));
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Filesystem\Providers\FilesServiceProvider;

class FilesServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new FilesServiceProvider());

        self::assertInstanceOf(Filesystem::class, $container->get(Filesystem::class));
        self::assertInstanceOf(Filesystem::class, $container->get(FilesystemContract::class));
        self::assertInstanceOf(Filesystem::class, $container->get('files'));
    }
}

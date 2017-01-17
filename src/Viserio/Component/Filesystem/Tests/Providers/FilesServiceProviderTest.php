<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Filesystem\Providers\FilesServiceProvider;

class FilesServiceProviderTest extends TestCase
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

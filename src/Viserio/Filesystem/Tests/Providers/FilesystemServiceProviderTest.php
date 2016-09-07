<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Providers;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Filesystem\FilesystemAdapter;
use Viserio\Filesystem\FilesystemManager;
use Viserio\Filesystem\Providers\FilesystemServiceProvider;

class FilesystemServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new FilesystemServiceProvider());

        $container->get('config')->setArray([
            'filesystem.connections' => [
                'local' => [
                    'path' => __DIR__, 'prefix' => 'your-prefix',
                ],
            ],
        ]);

        $this->assertInstanceOf(FilesystemManager::class, $container->get(FilesystemManager::class));
        $this->assertInstanceOf(FilesystemManager::class, $container->get(Filesystem::class));
        $this->assertInstanceOf(FilesystemManager::class, $container->get(FilesystemInterface::class));
        $this->assertInstanceOf(FilesystemManager::class, $container->get('flysystem'));
        $this->assertInstanceOf(FilesystemAdapter::class, $container->get('flysystem.connection'));
    }
}

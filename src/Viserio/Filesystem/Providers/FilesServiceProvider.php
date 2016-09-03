<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Filesystem\Filesystem;

class FilesServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Filesystem::class => [self::class, 'createFilesystem'],
            'files' => function (ContainerInterface $container) {
                return $container->get(Filesystem::class);
            },
            FilesystemContract::class => function (ContainerInterface $container) {
                return $container->get(Filesystem::class);
            },
        ];
    }

    public static function createFilesystem(): Filesystem
    {
        return new Filesystem();
    }
}

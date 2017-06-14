<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Filesystem\Filesystem;

class FilesServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            FilesystemContract::class => [self::class, 'createFilesystem'],
            'files'                   => function (ContainerInterface $container) {
                return $container->get(Filesystem::class);
            },
            Filesystem::class         => function (ContainerInterface $container) {
                return $container->get(FilesystemContract::class);
            },
        ];
    }

    public static function createFilesystem(): Filesystem
    {
        return new Filesystem();
    }
}

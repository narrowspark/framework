<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Filesystem\Filesystem;

class FilesServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            FilesystemContract::class => static function (): Filesystem {
                return new Filesystem();
            },
            'files' => static function (ContainerInterface $container) {
                return $container->get(Filesystem::class);
            },
            Filesystem::class => static function (ContainerInterface $container) {
                return $container->get(FilesystemContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }
}

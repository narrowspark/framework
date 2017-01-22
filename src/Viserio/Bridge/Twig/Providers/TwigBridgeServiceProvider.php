<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\View\Engines\EngineResolver;
use Viserio\Bridge\Twig\Engine\TwigEngine;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;

class TwigBridgeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EngineResolver::class => [self::class, 'createEngineResolver'],
        ];
    }

    public static function createEngineResolver(ContainerInterface $container): EngineResolver
    {
        $engines = $container->get(EngineResolver::class);

        $engines->register('twig', function () use ($container) {
            return new TwigEngine($container);
        });

        return $engines;
    }
}

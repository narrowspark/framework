<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Providers;

use Interop\Container\ServiceProvider;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Routing\Dispatchers\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Dispatchers\SimpleDispatcher;
use Viserio\Component\Routing\Generator\UrlGenerator;
use Viserio\Component\Routing\Router;

class RoutingServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            DispatcherContract::class   => [self::class, 'createRouteDispatcher'],
            RouterContract::class       => [self::class, 'createRouter'],
            'route'                     => function (ContainerInterface $container) {
                return $container->get(Router::class);
            },
            'router'                    => function (ContainerInterface $container) {
                return $container->get(RouterContract::class);
            },
            Router::class => function (ContainerInterface $container) {
                return $container->get(RouterContract::class);
            },
            UrlGeneratorContract::class => [self::class, 'createUrlGenerator'],
            UrlGenerator::class         => function (ContainerInterface $container) {
                return $container->get(UrlGeneratorContract::class);
            },
        ];
    }

    /**
     * Create a route dispatcher instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable             $getPrevious
     *
     * @return \Viserio\Component\Contracts\Routing\Dispatcher
     */
    public static function createRouteDispatcher(ContainerInterface $container, ?callable $getPrevious = null): DispatcherContract
    {
        if (is_callable($getPrevious)) {
            $dispatcher = $getPrevious();
        } elseif (class_exists(Pipeline::class)) {
            $dispatcher = new MiddlewareBasedDispatcher();
        } else {
            $dispatcher = new SimpleDispatcher();
        }

        if ($container->has(EventManagerContract::class)) {
            $dispatcher->setEventManager($container->get(EventManagerContract::class));
        }

        return $dispatcher;
    }

    /**
     * Create a router instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Routing\Router
     */
    public static function createRouter(ContainerInterface $container): RouterContract
    {
        $router = new Router($container->get(DispatcherContract::class));

        $router->setContainer($container);

        return $router;
    }

    /**
     * Create a url generator instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Routing\UrlGenerator
     */
    public static function createUrlGenerator(ContainerInterface $container): UrlGeneratorContract
    {
        return new UrlGenerator(
            $container->get(RouterContract::class)->getRoutes(),
            $container->get(ServerRequestInterface::class),
            $container->get(UriFactoryInterface::class)
        );
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;

class SetRequestForConsole implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(ApplicationContract $app): void
    {
        $app->register(new class() implements ServiceProvider {
            /**
             * {@inheritdoc}
             */
            public function getServices()
            {
                return [
                    ServerRequestInterface::class => [self::class, 'createRequest'],
                ];
            }

            /**
             * Create a new console request.
             *
             * @param \Interop\Container\ContainerInterface $container
             *
             * @return \Psr\Http\Message\ServerRequestInterface
             */
            public static function createRequest(ContainerInterface $container): ServerRequestInterface
            {
                $url = $container->get(RepositoryContract::class)->get('app.url', 'http://localhost');

                return $container->get(ServerRequestFactoryInterface::class)->createServerRequest('GET', $url);
            }
        });
    }
}

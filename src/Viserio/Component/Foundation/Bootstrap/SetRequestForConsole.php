<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Interop\Container\ServiceProvider;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;

class SetRequestForConsole implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $config = $kernel->getKernelConfigurations();

        $kernel->getContainer()->register(new class($config) implements ServiceProvider {
            /**
             * Config array.
             *
             * @var array
             */
            protected static $config;

            /**
             * Create a new server request instance.
             *
             * @param array $config
             */
            public function __construct(array $config)
            {
                self::$config = $config;
            }

            /**
             * {@inheritdoc}
             */
            public function getServices(): array
            {
                return [
                    ServerRequestInterface::class => [self::class, 'createRequest'],
                ];
            }

            /**
             * Create a new console request.
             *
             * @param \Psr\Container\ContainerInterface $container
             *
             * @return \Psr\Http\Message\ServerRequestInterface
             */
            public static function createRequest(ContainerInterface $container): ServerRequestInterface
            {
                return $container->get(ServerRequestFactoryInterface::class)->createServerRequest(
                    'GET',
                    self::$config['url']
                );
            }
        });
    }
}

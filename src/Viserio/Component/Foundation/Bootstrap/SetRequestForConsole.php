<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\ServerRequestFactoryInterface;
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
        $config = $kernel->getConfigurations();

        $kernel->getContainer()->register(new class($config) implements ServiceProvider {
            /**
             * Config array.
             *
             * @var array
             */
            protected $config;

            /**
             * Create a new server request instance.
             *
             * @param array $config
             */
            public function __construct(array $config)
            {
                $this->config = $config;
            }

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
                return $container->get(ServerRequestFactoryInterface::class)->createServerRequest(
                    'GET',
                    $this->config['app']['url']
                );
            }
        });
    }
}

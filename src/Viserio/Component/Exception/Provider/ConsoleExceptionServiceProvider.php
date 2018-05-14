<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Provider;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;
use Viserio\Component\Exception\Console\Handler;

class ConsoleExceptionServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            ConsoleHandlerContract::class => [self::class, 'createExceptionHandler'],
            Handler::class                => function (ContainerInterface $container) {
                return $container->get(ConsoleHandlerContract::class);
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

    /**
     * Create a new Handler instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Exception\ConsoleHandler
     */
    public static function createExceptionHandler(ContainerInterface $container): ConsoleHandlerContract
    {
        $logger = null;

        if ($container->has(LoggerInterface::class)) {
            $logger = $container->get(LoggerInterface::class);
        }

        return new Handler($container->get('config'), $logger);
    }
}

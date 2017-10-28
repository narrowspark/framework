<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Provider;

use Interop\Container\ServiceProviderInterface;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Log\Log;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Writer as MonologWriter;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class LoggerServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            MonologWriter::class => [self::class, 'createMonologWriter'],
            HandlerParser::class => [self::class, 'createHandlerParser'],
            'log'                => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            'logger' => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            Log::class => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            Logger::class => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            LoggerInterface::class => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
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
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'log'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return [
            'env',
        ];
    }

    /**
     * Create a handler parser instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Log\HandlerParser
     */
    public static function createHandlerParser(ContainerInterface $container): HandlerParser
    {
        $options = self::resolveOptions($container);

        return new HandlerParser(new Logger($options['env']));
    }

    /**
     * Create a monolog writer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Log\Writer
     */
    public static function createMonologWriter(ContainerInterface $container): MonologWriter
    {
        $logger = new MonologWriter($container->get(HandlerParser::class));

        if ($container->has(EventManagerContract::class)) {
            $logger->setEventManager($container->get(EventManagerContract::class));
        }

        return $logger;
    }
}

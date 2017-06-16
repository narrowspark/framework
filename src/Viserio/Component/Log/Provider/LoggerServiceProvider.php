<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Provider;

use Interop\Container\ServiceProvider;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Log\Log;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Writer as MonologWriter;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class LoggerServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use StaticOptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            MonologWriter::class => [self::class, 'createMonologWriter'],
            HandlerParser::class => [self::class, 'createHandlerParser'],
            'log'                => function (ContainerInterface $container) {
                return $container->get(MonologWriter::class);
            },
            'logger'             => function (ContainerInterface $container) {
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
    public function getDimensions(): iterable
    {
        return ['viserio', 'log'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
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

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}

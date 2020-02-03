<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\WebServer\Container\Provider;

use Monolog\Formatter\FormatterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\Server\DumpServer;
use Symfony\Component\VarDumper\VarDumper;
use Viserio\Bridge\Monolog\Formatter\ConsoleFormatter;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Console\ConsoleEvents;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\Argument\ArrayArgument;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\WebServer\Command\ServerDumpCommand;
use Viserio\Component\WebServer\Command\ServerLogCommand;
use Viserio\Component\WebServer\Command\ServerServeCommand;
use Viserio\Component\WebServer\Command\ServerStartCommand;
use Viserio\Component\WebServer\Command\ServerStatusCommand;
use Viserio\Component\WebServer\Command\ServerStopCommand;
use Viserio\Component\WebServer\Event\DumpListenerEvent;
use Viserio\Component\WebServer\RequestContextProvider;
use Viserio\Contract\Config\Exception\InvalidArgumentException;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Events\EventManager;

class WebServerServiceProvider implements ExtendServiceProviderContract,
    ProvidesDefaultConfigContract,
    RequiresComponentConfigContract,
    RequiresValidatedConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        if (\class_exists(ConsoleFormatter::class) && \interface_exists(FormatterInterface::class)) {
            $container->singleton(ServerLogCommand::class)
                ->addTag(AddConsoleCommandPipe::TAG);
        }

        if (\class_exists(VarDumper::class)) {
            $container->singleton(Connection::class)
                ->setArguments([
                    (new ConfigDefinition(self::class))
                        ->setKey('debug_server.host'),
                    new ArrayArgument([
                        'request' => new ReferenceDefinition(RequestContextProvider::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE),
                        'source' => new ReferenceDefinition(SourceContextProvider::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE),
                    ]),
                ]);

            $container->singleton(DumpServer::class)
                ->setArguments([
                    (new ConfigDefinition(self::class))
                        ->setKey('debug_server.host'),
                    new ReferenceDefinition(LoggerInterface::class, ReferenceDefinition::NULL_ON_INVALID_REFERENCE),
                ]);

            $container->singleton(DumpListenerEvent::class);
            $container->singleton(ServerDumpCommand::class)
                ->addTag(AddConsoleCommandPipe::TAG);
        }

        $container->singleton(ServerStatusCommand::class)
            ->addTag(AddConsoleCommandPipe::TAG);
        $container->singleton(ServerStopCommand::class)
            ->addTag(AddConsoleCommandPipe::TAG);

        $arguments = [
            (new ConfigDefinition(self::class))
                ->setKey('web_folder'),
            (new ConfigDefinition(self::class))
                ->setKey('env'),
        ];

        $container->singleton(ServerServeCommand::class)
            ->setArguments($arguments)
            ->addTag(AddConsoleCommandPipe::TAG);
        $container->singleton(ServerStartCommand::class)
            ->setArguments($arguments)
            ->addTag(AddConsoleCommandPipe::TAG);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            ServerRequestInterface::class => static function ($definition, ContainerBuilderContract $container): void {
                if (\interface_exists(ContextProviderInterface::class)) {
                    $container->singleton(ContextProviderInterface::class, RequestContextProvider::class)
                        ->addArgument(new ReferenceDefinition(ServerRequestInterface::class));

                    $container->setAlias(ContextProviderInterface::class, RequestContextProvider::class);
                }
            },
            EventManager::class => static function (ObjectDefinitionContract $definition): void {
                if (! class_exists(ConsoleEvents::class)) {
                    return;
                }

                // Register early to have a working dump() as early as possible
                $definition->addMethodCall('attach', [ConsoleEvents::COMMAND, [new ReferenceDefinition(DumpListenerEvent::class), 'configure'], 1024]);
            },
            ConsoleKernelContract::class => static function ($definition, ContainerBuilderContract $container): void {
                $arguments = [
                    (new ReferenceDefinition(ConsoleKernelContract::class))
                        ->addMethodCall('getPublicPath'),
                    (new ReferenceDefinition(ConsoleKernelContract::class))
                        ->addMethodCall('getEnvironment'),
                ];

                $container->getDefinition(ServerServeCommand::class)
                    ->setArguments($arguments);
                $container->getDefinition(ServerStartCommand::class)
                    ->setArguments($arguments);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'webserver'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'debug_server' => [
                'host' => 'tcp://127.0.0.1:9912',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigValidators(): iterable
    {
        return [
            'debug_server' => static function ($optionValue, $optionsKey): void {
                if (! \is_array($optionValue)) {
                    throw InvalidArgumentException::invalidType($optionsKey, $optionValue, ['array'], self::class);
                }

                if (isset($optionValue['host']) && ! \is_string($optionValue['host'])) {
                    throw InvalidArgumentException::invalidType('host', $optionValue['host'], ['string'], self::class);
                }
            },
            'web_folder' => ['string'],
            'env' => ['string'],
        ];
    }
}

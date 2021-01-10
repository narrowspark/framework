<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Translation\Container\Provider;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\TranslationManager;
use Viserio\Contract\Config\Exception\InvalidArgumentException;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Parser\Loader as LoaderContract;
use Viserio\Contract\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Contract\Translation\Translator as TranslatorContract;

class TranslationServiceProvider implements AliasServiceProviderContract,
    ExtendServiceProviderContract,
    ProvidesDefaultConfigContract,
    RequiresComponentConfigContract,
    RequiresValidatedConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'translation'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'locale' => 'en',
            'directories' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigValidators(): iterable
    {
        return [
            'locale' => ['string'],
            'directories' => static function ($optionValue, $optionKey): void {
                if (! \is_array($optionValue)) {
                    throw InvalidArgumentException::invalidType($optionKey, $optionValue, ['array'], self::class);
                }

                if (\count($optionValue) === 0) {
                    return;
                }

                foreach ($optionValue as $item) {
                    if (! \is_string($item)) {
                        throw new InvalidArgumentException(\sprintf('Invalid configuration value provided in [%s]; Expected array with string, but got [%s], in [%s].', $optionKey, (\is_object($item) ? \get_class($item) : \gettype($item)), self::class));
                    }
                }
            },
            'files' => static function ($optionValue, $optionKey): void {
                if (! \is_string($optionValue) && ! \is_array($optionValue)) {
                    throw InvalidArgumentException::invalidType($optionKey, $optionValue, ['string', 'array'], self::class);
                }

                if (\is_string($optionValue) || \count($optionValue) === 0) {
                    return;
                }

                foreach ($optionValue as $item) {
                    if (! \is_string($item)) {
                        throw new InvalidArgumentException(\sprintf('Invalid configuration value provided in [%s]; Expected array with string, but got [%s], in [%s].', $optionKey, (\is_object($item) ? \get_class($item) : \gettype($item)), self::class));
                    }
                }
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(MessageFormatterContract::class, IntlMessageFormatter::class);

        $container->singleton(TranslationManagerContract::class, TranslationManager::class)
            ->addMethodCall('setLocale', [
                (new ConfigDefinition(self::class))
                    ->setKey('locale'),
            ])
            ->addMethodCall('setDirectories', [
                (new ConfigDefinition(self::class))
                    ->setKey('directories'),
            ])
            ->addMethodCall('setLogger', [new ReferenceDefinition(PsrLoggerInterface::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)]);

        $container->singleton(TranslatorContract::class, [new ReferenceDefinition(TranslationManagerContract::class), 'getTranslator']);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            TranslationManagerContract::class => static function (ObjectDefinitionContract $definition, ContainerBuilderContract $container): void {
                if ($container->has(LoaderContract::class)) {
                    $definition
                        ->addMethodCall('setLoader', [
                            new ReferenceDefinition(LoaderContract::class),
                        ])
                        ->addMethodCall('import', [
                            (new ConfigDefinition(self::class))
                                ->setKey('files'),
                        ]);
                }
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            TranslationManager::class => TranslationManagerContract::class,
            'translator' => TranslatorContract::class,
        ];
    }
}

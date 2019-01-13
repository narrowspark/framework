<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;
use Viserio\Component\Contract\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Component\Contract\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\Translation\Formatter\IntlMessageFormatter;
use Viserio\Component\Translation\TranslationManager;

class TranslationServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            MessageFormatterContract::class   => [self::class, 'createMessageFormatter'],
            TranslationManagerContract::class => [self::class, 'createTranslationManager'],
            TranslationManager::class         => static function (ContainerInterface $container) {
                return $container->get(TranslationManagerContract::class);
            },
            TranslatorContract::class => [self::class, 'createTranslator'],
            'translator'              => static function (ContainerInterface $container) {
                return $container->get(TranslatorContract::class);
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
    public static function getDimensions(): array
    {
        return ['viserio', 'translation'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'locale'      => false,
            'directories' => false,
            'files'       => false,
        ];
    }

    /**
     * Create a new IntlMessageFormatter instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Translation\Formatter\IntlMessageFormatter
     */
    public static function createMessageFormatter(ContainerInterface $container): IntlMessageFormatter
    {
        return new IntlMessageFormatter();
    }

    /**
     * Create a new TranslationManager instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Translation\TranslationManager
     */
    public static function createTranslationManager(ContainerInterface $container): TranslationManagerContract
    {
        $options = self::resolveOptions($container->get('config'));

        $manager = new TranslationManager($container->get(MessageFormatterContract::class));

        if ($container->has(LoaderContract::class)) {
            $manager->setLoader($container->get(LoaderContract::class));
        }

        if (isset($options['locale'])) {
            $manager->setLocale($options['locale']);
        }

        if (isset($options['directories'])) {
            $manager->setDirectories($options['directories']);
        }

        if (isset($options['files'])) {
            foreach ((array) $options['files'] as $import) {
                $manager->import($import);
            }
        }

        if ($container->has(PsrLoggerInterface::class)) {
            $manager->setLogger($container->get(PsrLoggerInterface::class));
        }

        return $manager;
    }

    /**
     * Create a new Translation instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Translation\Translator
     */
    public static function createTranslator(ContainerInterface $container): TranslatorContract
    {
        return $container->get(TranslationManager::class)->getTranslator();
    }
}

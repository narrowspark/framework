<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Translation\MessageFormatter as MessageFormatterContract;
use Viserio\Component\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\Translation\Formatters\IntlMessageFormatter;
use Viserio\Component\Translation\TranslationManager;

class TranslationServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options = [];

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            MessageFormatterContract::class   => [self::class, 'createMessageFormatter'],
            TranslationManagerContract::class => [self::class, 'createTranslationManager'],
            TranslationManager::class         => function (ContainerInterface $container) {
                return $container->get(TranslationManagerContract::class);
            },
            TranslatorContract::class         => [self::class, 'createTranslator'],
            'translator'                      => function (ContainerInterface $container) {
                return $container->get(TranslatorContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'translation'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
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
     * @return \Viserio\Component\Translation\Formatters\IntlMessageFormatter
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
     * @return \Viserio\Component\Contracts\Translation\TranslationManager
     */
    public static function createTranslationManager(ContainerInterface $container): TranslationManagerContract
    {
        self::resolveOptions($container);

        $manager = new TranslationManager($container->get(MessageFormatterContract::class));

        if ($container->has(LoaderContract::class)) {
            $manager->setLoader($container->get(LoaderContract::class));
        }

        if ($locale = self::$options['locale']) {
            $manager->setLocale($locale);
        }

        if ($directories = self::$options['directories']) {
            $manager->setDirectories($directories);
        }

        if ($imports = self::$options['files']) {
            foreach ((array) $imports as $import) {
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
     * @return \Viserio\Component\Contracts\Translation\TranslationManager
     */
    public static function createTranslator(ContainerInterface $container): TranslatorContract
    {
        return $container->get(TranslationManager::class)->getTranslator();
    }

    /**
     * Resolve component options.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (count(self::$options) === 0) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}

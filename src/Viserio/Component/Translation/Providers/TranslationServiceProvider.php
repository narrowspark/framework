<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Translation\MessageSelector;
use Viserio\Component\Translation\PluralizationRules;
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
    private static $options;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            TranslationManagerContract::class => [self::class, 'createTranslationManager'],
            TranslationManager::class         => function (ContainerInterface $container) {
                return $container->get(TranslationManagerContract::class);
            },
            TranslatorContract::class => [self::class, 'createTranslator'],
            'translator'              => function (ContainerInterface $container) {
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

    public static function createTranslationManager(ContainerInterface $container): TranslationManager
    {
        self::resolveOptions($container);

        $manager = new TranslationManager(
            new PluralizationRules(),
            new MessageSelector()
        );

        $manager->setLoader($container->get(FileLoader::class));

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

    public static function createTranslator(ContainerInterface $container): TranslatorContract
    {
        return $container->get(TranslationManager::class)->getTranslator();
    }

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Translation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Parsers\FileLoader;
use Viserio\Translation\MessageSelector;
use Viserio\Translation\PluralizationRules;
use Viserio\Translation\TranslationManager;

class TranslatorServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.translation';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            TranslationManager::class => [self::class, 'createTranslationManager'],
            TranslationManagerContract::class  => function (ContainerInterface $container) {
                return $container->get(TranslationManager::class);
            },
            'translator' => [self::class, 'createTranslator'],
            TranslatorContract::class => function (ContainerInterface $container) {
                return $container->get('translator');
            },
        ];
    }

    public static function createTranslationManager(ContainerInterface $container): TranslationManager
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('translation');
        } else {
            $config = self::get($container, 'options');
        }

        $manager = new TranslationManager(
            new PluralizationRules(),
            new MessageSelector()
        );
        $manager->setLoader($container->get(FileLoader::class));
        $manager->setLocale($config['locale']);
        $manager->import($config['path.lang']);

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
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name, $default = null)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}

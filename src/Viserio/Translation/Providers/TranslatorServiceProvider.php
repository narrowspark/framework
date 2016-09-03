<?php
declare(strict_types=1);
namespace Viserio\Translation\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Translation\TranslationManager;
use Viserio\Translation\PluralizationRules;

class TranslatorServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        $this->app->bind('translator.pluralization.rules', function () {
            return new PluralizationRules();
        });

        $this->app->bind('translator.message.selector', function () {
            return new MessageSelector();
        });

        $this->app->singleton('translator', function ($app) {
            $translator = new Manager(
                $app->get('files'),
                $app->get('translator.pluralization.rules'),
                $app->get('translator.message.selector')
            );

            $translator->setLocale($app->get('config')->get('app::locale'));

            return $translator;
        });
    }

    public static function createTranslationManager(ContainerInterface $container): AliasLoader
    {
        return new TranslationManager(
            new PluralizationRules(),
            new MessageSelector()
        );
    }
}

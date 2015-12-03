<?php
namespace Viserio\Translator\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Translator\Manager;
use Viserio\Translator\PluralizationRules;

/**
 * TranslatorServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class TranslatorServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
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

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Load lang files
        if (($langFiles = $this->app->get('config')->get('app::language.files')) !== null) {
            foreach ($langFiles as $file => $lang) {
                $this->app->get('translator')->bind(
                    $file.'.'.$lang['ext'],
                    $lang['group'],
                    $lang['env'],
                    $lang['namespace']
                );
            }
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'translator',
            'translator.pluralization.rules',
            'translator.message.selector',
        ];
    }
}

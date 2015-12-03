<?php
namespace Viserio\Encryption\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Encryption\Encrypter;

class EncrypterServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('encrypter', function ($app) {
            $config = $app->get('config');

            $encrypt = new Encrypter(
                $app->get('hash'),
                $app->get('hash.rand.generator'),
                $config->get('app::crypt.key'),
                $config->get('app::crypt.cipher'),
                $config->get('app::crypt.mode')
            );

            return $encrypt;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'encrypter',
        ];
    }
}

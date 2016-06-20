<?php
namespace Viserio\Encryption\Providers;

use Defuse\Crypto\Key;
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
                Key::loadFromAsciiSafeString(
                    $config->get('app::key')
                )
            );

            return $encrypt;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'encrypter',
        ];
    }
}

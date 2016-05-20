<?php
namespace Viserio\Hashing\Providers;

use Defuse\Crypto\Key;
use Viserio\Application\ServiceProvider;
use Viserio\Hashing\Generator as HashGenerator;
use Viserio\Hashing\Password;

class HashingServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerPassword();
    }

    protected function registerPassword()
    {
        $this->app->singleton('password', function () {
            $config = $app->get('config');

            return new Password(
                Key::loadFromAsciiSafeString(
                    $config->get('app::key')
                )
            );
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
            'password',
        ];
    }
}

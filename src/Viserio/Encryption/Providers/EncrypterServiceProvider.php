<?php
namespace Viserio\Encryption\Providers;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Viserio\Application\ServiceProvider;
use Viserio\Encryption\Encrypter;

/**
 * EncrypterServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
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

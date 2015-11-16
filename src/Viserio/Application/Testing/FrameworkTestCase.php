<?php
namespace Viserio\Application\Testing;

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

use Viserio\Application\Traits\ServiceProviderTrait;
use Viserio\Container\Container;

/**
 * FrameworkTestCase.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8-dev
 */
abstract class FrameworkTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Container instanve.
     *
     * @var \Viserio\Container\Container
     */
    protected $container;

    use ServiceProviderTrait;

    /**
     * When overriding this method, make sure you call parent::setUp().
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();

        $this->start();
    }

    /**
     * Run extra setup code.
     */
    protected function start()
    {
        $providers = [
            'Brainwave\Cache\Providers\CacheServiceProvider' => [],
            'Brainwave\Console\Providers\ConsoleServiceProvider' => [],
            'Brainwave\Cookie\Providers\CookieServiceProvider' => [],
            //'Brainwave\Database\Providers\DatabaseServiceProvider'               => [],
            'Brainwave\Encrypter\Providers\EncrypterServiceProvider' => [],
            'Brainwave\Events\Providers\EventsServiceProvider' => [],
            'Brainwave\Hashing\Providers\HashingServiceProvider' => [],
            'Brainwave\Log\Providers\LoggerServiceProvider' => [],
            'Brainwave\Routing\Providers\RoutingServiceProvider' => [],
            'Brainwave\Session\Providers\SessionServiceProvider' => [],
            'Brainwave\Support\Providers\AutoloaderServiceProvider' => [],
            'Brainwave\Support\Providers\SupportServiceProvider' => [],
            'Brainwave\Support\Providers\DebugServiceProvider' => [],
            'Brainwave\Translator\Providers\TranslatorServiceProvider' => [
                'translator.path' => '',
            ],
            'Brainwave\View\Providers\ViewServiceProvider' => [],
        ];

        foreach ($services as $provider => $arr) {
            $this->register(new $provider($this), $arr);
        }
    }

    public function bind($alias, $concrete)
    {
        $this->bind($alias, $concrete);
    }
}

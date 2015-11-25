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
 * @version     0.10.0
 */

use Viserio\Application\Traits\ServiceProviderTrait;
use Viserio\Container\Container;

/**
 * FrameworkTestCase.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8
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
            'Viserio\Cache\Providers\CacheServiceProvider' => [],
            'Viserio\Console\Providers\ConsoleServiceProvider' => [],
            'Viserio\Cookie\Providers\CookieServiceProvider' => [],
            //'Viserio\Database\Providers\DatabaseServiceProvider'               => [],
            'Viserio\Encrypter\Providers\EncrypterServiceProvider' => [],
            'Viserio\Events\Providers\EventsServiceProvider' => [],
            'Viserio\Hashing\Providers\HashingServiceProvider' => [],
            'Viserio\Log\Providers\LoggerServiceProvider' => [],
            'Viserio\Routing\Providers\RoutingServiceProvider' => [],
            'Viserio\Session\Providers\SessionServiceProvider' => [],
            'Viserio\Support\Providers\AutoloaderServiceProvider' => [],
            'Viserio\Support\Providers\SupportServiceProvider' => [],
            'Viserio\Support\Providers\DebugServiceProvider' => [],
            'Viserio\Translator\Providers\TranslatorServiceProvider' => [
                'translator.path' => '',
            ],
            'Viserio\View\Providers\ViewServiceProvider' => [],
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

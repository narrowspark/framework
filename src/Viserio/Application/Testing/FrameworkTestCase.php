<?php
declare(strict_types=1);
namespace Viserio\Application\Testing;

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
    use ServiceProviderTrait;
    /**
     * Container instanve.
     *
     * @var \Viserio\Container\Container
     */
    protected $container;

    /**
     * When overriding this method, make sure you call parent::setUp().
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();

        $this->start();
    }

    public function bind($alias, $concrete)
    {
        $this->bind($alias, $concrete);
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
}

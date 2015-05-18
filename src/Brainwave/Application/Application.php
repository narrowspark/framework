<?php

namespace Brainwave\Application;

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
 * @version     0.9.8-dev
 */

use Brainwave\Application\Traits\BootableTrait;
use Brainwave\Application\Traits\EnvironmentTrait;
use Brainwave\Application\Traits\HttpErrorHandlingTrait;
use Brainwave\Application\Traits\HttpHandlingTrait;
use Brainwave\Application\Traits\MiddlewaresTrait;
use Brainwave\Application\Traits\PathsTrait;
use Brainwave\Application\Traits\ServiceProviderTrait;
use Brainwave\Container\Container;
use Brainwave\Contracts\Application\Foundation;
use Brainwave\Support\StaticalProxyManager;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Application.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Application extends Container implements Foundation, HttpKernelInterface
{
    /**
     * The Brainwave framework version.
     *
     * @var string
     */
    const VERSION = '0.9.8-dev';

    // Register all needed Traits
    use BootableTrait;
    use EnvironmentTrait;
    use HttpErrorHandlingTrait;
    use HttpHandlingTrait;
    use MiddlewaresTrait;
    use PathsTrait;
    use ServiceProviderTrait;

    /**
     * Instantiate a new Application.
     *
     * Let's start make magic!
     *
     * @param array $paths
     */
    public function __construct(array $paths)
    {
        parent::__construct();

        $this->registerFacade();

        /*
         * Here we are binding the paths configured in paths.php to the app. You
         * should not be changing these here. If you need to change these you
         * may do so within the paths.php file and they will be bound here.
         */
        $this->bindInstallPaths($paths);

        // App setting
        $this->bind('env', '');

        $this->middlewares = new \SplPriorityQueue();

        $this->registerCoreContainerAliases();
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();

        //Register providers
        foreach ($this->get('config')->get('services::providers') as $provider => $arr) {
            $this->register(new $provider($this), $arr);
        }
    }

    /**
     * Register all of the base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register('Brainwave\Http\Providers\ResponseServiceProvider');

        $this->register('Brainwave\Http\Providers\RequestServiceProvider');

        $this->register('Brainwave\Filesystem\Providers\FilesystemServiceProvider');

        $this->register('Brainwave\Application\Providers\ApplicationServiceProvider');

        $this->register('Brainwave\Exception\Providers\ExceptionServiceProvider');
    }

    /**
     * The facades provide a terser static interface over the various parts
     * of the application, allowing their methods to be accessed through
     * a mixtures of magic methods and facade derivatives. It's slick.
     */
    public function registerFacade()
    {
        StaticalProxyManager::setFacadeApplication($this);
        StaticalProxyManager::clearResolvedInstances();
    }

    /**
     * Escapes a text for HTML.
     *
     * @param string      $text         The input text to be escaped
     * @param int         $flags        The flags (@see htmlspecialchars)
     * @param string|null $charset      The charset
     * @param Boolean     $doubleEncode Whether to try to avoid double escaping or not
     *
     * @return string Escaped text
     */
    public function escape($text, $flags = ENT_COMPAT, $charset = null, $doubleEncode = true)
    {
        return htmlspecialchars($text, $flags, $charset === null ? $this->get('charset') : $charset, $doubleEncode);
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->get('config')->get('app::locale', 'en');
    }

    /**
     * Set the current application locale.
     *
     * @param string $locale
     *
     * @return Application
     */
    public function setLocale($locale)
    {
        $this->get('config')->set('app::locale', $locale);

        $this->get('translator')->setLocale($locale);

        return $this;
    }

    /**
     * Register the basic bindings into the container.
     */
    protected function registerBaseBindings()
    {
        $this->singleton('app', function () {
            return $this;
        });

        $this->bind('\Brainwave\Container\Container');
    }

    /**
     * Register the core class aliases in the container.
     */
    public function registerCoreContainerAliases()
    {
        $aliasList = [
            'app' => ['Brainwave\Application\Application', 'Brainwave\Contracts\Container\Container', 'Brainwave\Contracts\Application\Foundation'],
            'alias' => 'Brainwave\Application\AliasLoader',
            'autoloader' => 'Brainwave\Support\Autoloader',
            'cache' => ['Brainwave\Cache\CacheManager', 'Brainwave\Contracts\Cache\Factory'],
            'cache.store' => ['Brainwave\Cache\Repository', 'Brainwave\Contracts\Cache\Repository'],
            'config' => ['Brainwave\Config\Manager', 'Brainwave\Contracts\Config\Manager'],
            'config.repository' => ['Brainwave\Config\Repository', 'Brainwave\Contracts\Config\Repository'],
            'command.resolver' => 'Brainwave\Console\Command\CommandResolver',
            'cookie' => ['Brainwave\Cookie\CookieJar', 'Brainwave\Contracts\Cookie\Factory'],
            'db' => 'Brainwave\Database\DatabaseManager',
            'db.factory' => 'Brainwave\Database\Connection\ConnectionFactory',
            'dump' => 'Brainwave\Support\Debug\Dumper',
            'encrypter' => ['Brainwave\Encrypter\Encrypter', 'Brainwave\Contracts\Encrypter\Encrypter'],
            'events' => 'Brainwave\Events\Dispatcher',
            'environment' => 'Brainwave\Application\EnvironmentDetector',
            'exception' => 'Brainwave\Exception\Handler',
            'file.loader' => 'Brainwave\Filesystem\FileLoader',
            'files' => 'Brainwave\Filesystem\Filesystem',
            'filesystem' => ['Brainwave\Filesystem\FilesystemManager', 'Brainwave\Contracts\Filesystem\FilesystemManager'],
            'filesystem.factory' => 'Brainwave\Filesystem\Adapters\ConnectionFactory',
            'filesystem.disk' => 'Brainwave\Contracts\Filesystem\Filesystem',
            'filesystem.cloud' => 'Brainwave\Contracts\Filesystem\Filesystem',
            'hash' => 'Brainwave\Hashing\Generator',
            'logger' => ['Brainwave\Log\Writer', 'Brainwave\Contracts\Logging\Log', 'Psr\Log\LoggerInterface'],
            'mailer' => ['Brainwave\Mail\Mailer', 'Brainwave\Contracts\Mail\Mailer'],
            'request' => ['Brainwave\Http\Request', 'Brainwave\Contracts\Http\Request'],
            'response' => ['Brainwave\Http\Response', 'Brainwave\Contracts\Http\Response'],
            'password' => 'Brainwave\Hashing\Password',
            'statical.resolver' => 'Brainwave\Support\StaticalProxyResolver',
            'translator' => ['Brainwave\Translator\Manager'],
            'route' => 'Brainwave\Routing\RouteCollection',
            'route.url.generator' => 'Brainwave\Routing\UrlGenerator\SimpleUrlGenerator',
            'route.url.data.generator' => 'Brainwave\Routing\UrlGenerator\GroupCountBasedDataGenerator',
            'view' => ['Brainwave\View\Factory', 'Brainwave\Contracts\View\Factory'],
        ];

        foreach ($aliasList as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Run the application.
     *
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     * @param bool                                           $send
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run(SymfonyRequest $request = null, $send = true)
    {
        if ($request === null) {
            $request = SymfonyRequest::createFromGlobals();
        }

        $response = $this->handle($request);

        return $send ? $response->send() : $response;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(SymfonyRequest $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (!$this->booted) {
            $this->boot();
        }

        return $this->resolveKernel()->handle($request, $type, $catch);
    }

    /**
     * Register shutdown.
     */
    public function shutdown()
    {
        $this->get('exception')->unregister();
    }
}

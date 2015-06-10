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
 * @version     0.10.0-dev
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
            'app' => [
                \Brainwave\Application\Application::class,
                \Brainwave\Contracts\Container\Container::class,
                \Brainwave\Contracts\Application\Foundation::class,
            ],
            'alias'      => \Brainwave\Application\AliasLoader::class,
            'autoloader' => \Brainwave\Support\Autoloader::class,
            'cache' => [
                \Brainwave\Cache\CacheManager::class,
                \Brainwave\Contracts\Cache\Factory::class,
            ],
            'cache.store' => [
                \Brainwave\Cache\Repository::class,
                \Brainwave\Contracts\Cache\Repository::class,
            ],
            'config' => [
                \Brainwave\Config\Manager::class,
                \Brainwave\Contracts\Config\Manager::class,
            ],
            'config.repository' => [
                \Brainwave\Config\Repository::class,
                \Brainwave\Contracts\Config\Repository::class,
            ],
            'command.resolver' => \Brainwave\Console\Command\CommandResolver::class,
            'cookie' => [
                \Brainwave\Cookie\CookieJar::class,
                \Brainwave\Contracts\Cookie\Factory::class,
            ],
            'db'         => \Brainwave\Database\DatabaseManager::class,
            'db.factory' => \Brainwave\Database\Connection\ConnectionFactory::class,
            'dump'       => \Brainwave\Support\Debug\Dumper::class,
            'encrypter' => [
                \Brainwave\Encrypter\Encrypter::class,
                \Brainwave\Contracts\Encrypter\Encrypter::class,
            ],
            'events'      => \Brainwave\Events\Dispatcher::class,
            'environment' => \Brainwave\Application\EnvironmentDetector::class,
            'exception'   => \Brainwave\Exception\Handler::class,
            'file.loader' => \Brainwave\Filesystem\FileLoader::class,
            'files'       => \Brainwave\Filesystem\Filesystem::class,
            'filesystem' => [
                \Brainwave\Filesystem\FilesystemManager::class,
                \Brainwave\Contracts\Filesystem\FilesystemManager::class,
            ],
            'filesystem.factory' => \Brainwave\Filesystem\Adapters\ConnectionFactory::class,
            'filesystem.disk'    => \Brainwave\Contracts\Filesystem\Filesystem::class,
            'filesystem.cloud'   => \Brainwave\Contracts\Filesystem\Filesystem::class,
            'hash'               => \Brainwave\Hashing\Generator::class,
            'logger' => [
                \Brainwave\Log\Writer::class,
                \Brainwave\Contracts\Logging\Log::class,
                \Psr\Log\LoggerInterface::class,
            ],
            'mailer' => [
                \Brainwave\Mail\Mailer::class,
                \Brainwave\Contracts\Mail\Mailer::class,
            ],
            'request' => [
                \Brainwave\Http\Request::class,
                \Brainwave\Contracts\Http\Request::class,
            ],
            'response' => [
                \Brainwave\Http\Response::class,
                \Brainwave\Contracts\Http\Response::class,
            ],
            'password'          => \Brainwave\Hashing\Password::class,
            'statical.resolver' => \Brainwave\Support\StaticalProxyResolver::class,
            'translator' => [
                \Brainwave\Translator\Manager::class,
            ],
            'route'                    => \Brainwave\Routing\RouteCollection::class,
            'route.url.generator'      => \Brainwave\Routing\UrlGenerator\SimpleUrlGenerator::class,
            'route.url.data.generator' => \Brainwave\Routing\UrlGenerator\GroupCountBasedDataGenerator::class,
            'view' => [
                \Brainwave\View\Factory::class,
                \Brainwave\Contracts\View\Factory::class,
            ],
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

<?php
namespace Viserio\Application;

use SplPriorityQueue;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Viserio\Application\Application;
use Viserio\Application\Traits\BootableTrait;
use Viserio\Application\Traits\EnvironmentTrait;
use Viserio\Application\Traits\HttpErrorHandlingTrait;
use Viserio\Application\Traits\HttpHandlingTrait;
use Viserio\Application\Traits\MiddlewaresTrait;
use Viserio\Application\Traits\PathsTrait;
use Viserio\Application\Traits\ServiceProviderTrait;
use Viserio\Container\Container;
use Viserio\Contracts\Application\Foundation;
use Viserio\StaticalProxy\StaticalProxy;

class Application extends Container implements Foundation, HttpKernelInterface
{
    /**
     * The Viserio framework version.
     *
     * @var string
     */
    const VERSION = '0.10.0';

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

        $this->middlewares = new SplPriorityQueue();

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
        $this->register('Viserio\Http\Providers\ResponseServiceProvider');

        $this->register('Viserio\Http\Providers\RequestServiceProvider');

        $this->register('Viserio\Filesystem\Providers\FilesystemServiceProvider');

        $this->register('Viserio\Application\Providers\ApplicationServiceProvider');

        $this->register('Viserio\Exception\Providers\ExceptionServiceProvider');
    }

    /**
     * The facades provide a terser static interface over the various parts
     * of the application, allowing their methods to be accessed through
     * a mixtures of magic methods and facade derivatives. It's slick.
     */
    public function registerFacade()
    {
        StaticalProxy::setFacadeApplication($this);
        StaticalProxy::clearResolvedInstances();
    }

    /**
     * Escapes a text for HTML.
     *
     * @param string      $text         The input text to be escaped
     * @param int         $flags        The flags (@see htmlspecialchars)
     * @param string|null $charset      The charset
     * @param bool        $doubleEncode Whether to try to avoid double escaping or not
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

        $this->bind('\Viserio\Container\Container');
    }

    /**
     * Register the core class aliases in the container.
     */
    public function registerCoreContainerAliases()
    {
        $aliasList = [
            'app' => [
                Application::class,
                \Viserio\Contracts\Container\Container::class,
                \Viserio\Contracts\Application\Foundation::class,
            ],
            'alias'      => \Viserio\Application\AliasLoader::class,
            'autoloader' => \Viserio\Support\Autoloader::class,
            'cache' => [
                \Viserio\Cache\CacheManager::class,
                \Viserio\Contracts\Cache\Factory::class,
            ],
            'cache.store' => [
                \Viserio\Cache\Repository::class,
                \Viserio\Contracts\Cache\Repository::class,
            ],
            'config' => [
                \Viserio\Config\Manager::class,
                \Viserio\Contracts\Config\Manager::class,
            ],
            'config.repository' => [
                \Viserio\Config\Repository::class,
                \Viserio\Contracts\Config\Repository::class,
            ],
            'command.resolver' => \Viserio\Console\Command\CommandResolver::class,
            'cookie' => [
                \Viserio\Cookie\CookieJar::class,
                \Viserio\Contracts\Cookie\Factory::class,
            ],
            'db'         => \Viserio\Database\DatabaseManager::class,
            'db.factory' => \Viserio\Database\Connection\ConnectionFactory::class,
            'dump'       => \Viserio\Support\Debug\Dumper::class,
            'encrypter' => [
                \Viserio\Encrypter\Encrypter::class,
                \Viserio\Contracts\Encrypter\Encrypter::class,
            ],
            'events'      => \Viserio\Events\Dispatcher::class,
            'environment' => \Viserio\Application\EnvironmentDetector::class,
            'exception'   => \Viserio\Exception\Handler::class,
            'file.loader' => \Viserio\Filesystem\FileLoader::class,
            'files'       => \Viserio\Filesystem\Filesystem::class,
            'filesystem' => [
                \Viserio\Filesystem\FilesystemManager::class,
                \Viserio\Contracts\Filesystem\FilesystemManager::class,
            ],
            'filesystem.factory' => \Viserio\Filesystem\Adapters\ConnectionFactory::class,
            'filesystem.disk'    => \Viserio\Contracts\Filesystem\Filesystem::class,
            'filesystem.cloud'   => \Viserio\Contracts\Filesystem\Filesystem::class,
            'hash'               => \Viserio\Hashing\Generator::class,
            'logger' => [
                \Viserio\Log\Writer::class,
                \Viserio\Contracts\Logging\Log::class,
                \Psr\Log\LoggerInterface::class,
            ],
            'mailer' => [
                \Viserio\Mail\Mailer::class,
                \Viserio\Contracts\Mail\Mailer::class,
            ],
            'request' => [
                \Viserio\Http\Request::class,
                \Viserio\Contracts\Http\Request::class,
            ],
            'response' => [
                \Viserio\Http\Response::class,
                \Viserio\Contracts\Http\Response::class,
            ],
            'password'          => \Viserio\Hashing\Password::class,
            'statical.resolver' => \Viserio\Support\StaticalProxyResolver::class,
            'translator' => [
                \Viserio\Translator\Manager::class,
            ],
            'route'                    => \Viserio\Routing\RouteCollection::class,
            'route.url.generator'      => \Viserio\Routing\UrlGenerator\SimpleUrlGenerator::class,
            'route.url.data.generator' => \Viserio\Routing\UrlGenerator\GroupCountBasedDataGenerator::class,
            'view' => [
                \Viserio\View\Factory::class,
                \Viserio\Contracts\View\Factory::class,
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

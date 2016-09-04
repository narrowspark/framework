<?php
declare(strict_types=1);
namespace Viserio\Foundation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Container\Container;
use Viserio\Routing\Providers\RoutingServiceProvider;
use Viserio\Contracts\Foundation\Emitter as EmitterContract;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\Foundation\Http\Emitter;

class Application extends Container
{
    /**
     * The Viserio framework version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Create a new application instance.
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

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
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
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Run the application.
     *
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param bool                                $silent
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response = null, bool $silent = false): ResponseInterface
    {
        $response = $this->handle($request);

        return $send ? $response->send() : $response;
    }

    /**
     * Register shutdown.
     */
    public function shutdown()
    {
        $this->get('exception')->unregister();
    }

    /**
     * Register all of the base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider());
        $this->register(new RoutingServiceProvider());
    }

    /**
     * Register the basic bindings into the container.
     */
    protected function registerBaseBindings()
    {
        $this->singleton('app', function () {
            return $this;
        });

        $this->instance(Container::class, $this);
        $this->instance(EmitterContract::class, new Emitter());
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Foundation;

use Closure;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Contracts\Foundation\Emitter as EmitterContract;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Foundation\Http\Emitter;
use Viserio\Foundation\Traits\PathsTrait;
use Viserio\Parsers\Providers\ParsersServiceProvider;
use Viserio\Routing\Providers\RoutingServiceProvider;

class Application extends Container implements ApplicationContract
{
    use PathsTrait;

    /**
     * The Viserio framework version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string
     */
    protected $environmentFile = '.env';

    /**
     * The custom environment path defined by the developer.
     *
     * @var string
     */
    protected $environmentPath;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

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

        /*
         * Here we are binding the paths configured in paths.php to the app. You
         * should not be changing these here. If you need to change these you
         * may do so within the paths.php file and they will be bound here.
         */
        $this->bindInstallPaths($paths);

        $this->registerBaseServiceProviders();

        $this->registerBaseBindings();
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
     * Run the given array of bootstrap classes.
     *
     * @param array $bootstrappers
     */
    public function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            if ($this->has(DispatcherContract::class)) {
                $this->get(DispatcherContract::class)->trigger(
                    'bootstrapping.' . str_replace('\\', '', $bootstrapper),
                    [$this]
                );
            }

            $this->make($bootstrapper)->bootstrap($this);

            if ($this->has(DispatcherContract::class)) {
                $this->get(DispatcherContract::class)->trigger(
                    'bootstrapped.' . str_replace('\\', '', $bootstrapper),
                    [$this]
                );
            }
        }
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->get('path.base');
    }

    /**
     * Set the directory for the environment file.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useEnvironmentPath(string $path)
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * Set the environment file to be loaded during bootstrapping.
     *
     * @param string $file
     *
     * @return $this
     */
    public function loadEnvironmentFrom(string $file)
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * Get the environment file the application is using.
     *
     * @return string
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * Get the fully qualified path to the environment file.
     *
     * @return string
     */
    public function environmentFilePath(): string
    {
        return $this->environmentPath() . '/' . $this->environmentFile();
    }

    /**
     * Determine if the application configuration is cached.
     *
     * @return bool
     */
    public function configurationIsCached()
    {
        return file_exists($this->getCachedConfigPath());
    }

    /**
     * Detect the application's current environment.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    public function detectEnvironment(Closure $callback)
    {
        $args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;

        return $this['env'] = (new EnvironmentDetector())->detect($callback, $args);
    }

    /**
     * Register all of the base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new ConfigServiceProvider());
        $this->register(new EventsServiceProvider());
        $this->register(new ParsersServiceProvider());
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

        $this->instance(ApplicationContract::class, $this);

        $this->singleton(EmitterContract::class, Emitter::class);
    }
}

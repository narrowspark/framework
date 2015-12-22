<?php
namespace Viserio\Application\Traits;

use Viserio\Application\ServiceProvider;
use Viserio\Support\Arr;

/**
 * ServiceProviderTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7
 */
trait ServiceProviderTrait
{
    /**
     * Array of all service providers, even those that aren't registered.
     *
     * @var array
     */
    protected $serviceProviders = [];

    /**
     * Array of service providers that resolve instances in the container.
     *
     * @var array
     */
    protected $provides = [];

    /**
     * The names of the loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * A lookup of service providers by name.
     *
     * If the a provider is force-registered twice, only the first instance is included.
     *
     * @var array
     */
    protected $serviceProviderLookup = [];

    /**
     * {@inheritdoc}
     */
    public function register($provider, $options = [], $force = false)
    {
        if ((!is_string($provider)) && (!$provider instanceof ServiceProvider)) {
            throw new \Exception(
                'When registering a service provider, you must provide either and instance of ' .
                '[\Viserio\Container\ServiceProvider] or a fully qualified class name'
            );
        }

        if (($registered = $this->getProvider($provider)) && !$force) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        // Only allow a service provider to be registered once.
        if (in_array($provider, $this->serviceProviders, true)) {
            return;
        }

        $this->registerProvides($provider);
        $this->registerProvider($provider, $options);

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param ServiceProvider|string $provider
     *
     * @return ServiceProvider|null
     */
    public function getProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return isset($this->loadedProviders[$name]) ? $this->loadedProviders[$name] : null;
    }

    /**
     * Get the service providers that have been loaded.
     *
     * @return array
     */
    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    /**
     * Register a service provider.
     *
     * @param ServiceProvider $provider The service provider object.
     * @param array           $options
     */
    protected function registerProvider(ServiceProvider $provider, $options = [])
    {
        if (in_array($provider, $this->loadedProviders, true)) {
            return;
        }

        if (method_exists($provider, 'aliases')) {
            $this->aliases = array_merge($this->aliases, $provider->aliases());
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        // Once we have registered the service we will iterate through the options
        // and set each of them on the application so they will be available on
        // the actual loading of the service objects and for developer usage.
        foreach ($options as $key => $value) {
            $this->bind($key, $value);
        }

        // We also add this to a lookup, which makes getProvider nice and fast.
        $this->serviceProviderLookup = Arr::add($this->serviceProviderLookup, get_class($provider), get_class($provider));

        $this->markAsRegistered($provider);
    }

    /**
     * Mark the given provider as registered.
     *
     * @param ServiceProvider $provider
     */
    protected function markAsRegistered($provider)
    {
        $class = get_class($provider);
        $this->serviceProviders[$class] = $provider;
        $this->loadedProviders[$class] = true;

        $aliases = class_parents($class) + class_implements($class) + [$class];

        foreach ($aliases as $alias) {
            if (!isset($this->loadedProviders[$alias])) {
                $this->loadedProviders[$alias] = $provider;
            }
        }
    }

    /**
     * Remember the bindings that the specified service provider provides.
     *
     * @param ServiceProvider $provider The service provider object.
     */
    protected function registerProvides(ServiceProvider $provider)
    {
        foreach ($provider->provides() as $binding) {
            $this->provides[$binding][] = $provider;
        }
    }

    /**
     * {@inheritdoc}
     */
    abstract public function bind($alias, $concrete = null, $singleton = false);
}

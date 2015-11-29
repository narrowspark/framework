<?php
namespace Viserio\Container\Traits;

use Interop\Container\Definition\DefinitionProviderInterface;
use Viserio\Container\Interfaces\ContainerAwareInterface;
use Viserio\Support\Arr;

trait DefinitionProviderTrait
{
    /**
     * Array of all service providers, even those that aren't registered.
     *
     * @var array
     */
    protected $definitionProviders = [];

    /**
     * The names of the loaded service providers.
     *
     * @var array
     */
    protected $loadedDefinitions = [];

    /**
     * A lookup of service providers by name.
     *
     * If the a provider is force-registered twice, only the first instance is included.
     *
     * @var array
     */
    protected $definitionProviderLookup = [];

    /**
     * {@inheritdoc}
     */
    public function provider($provider, $options = [], $force = false)
    {
        if (($registered = $this->getProvider($provider)) && !$force) {
            return $registered;
        }

        if ($provider instanceof ContainerAwareInterface) {
            $provider = $provider->setContainer($this);
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
     * Get the registered definitions provider instance if it exists.
     *
     * @param DefinitionProviderInterface|string $provider
     *
     * @return DefinitionProviderInterface|null
     */
    public function getDefinitionProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return isset($this->$loadedDefinitions[$name]) ? $this->$loadedDefinitions[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitionProviders()
    {
        return $this->definitionProviders;
    }

    /**
     * Get the service providers that have been loaded.
     *
     * @return array
     */
    public function getLoadedDefinitionProviders()
    {
        return $this->$loadedDefinitions;
    }

    /**
     * Register a service provider.
     *
     * @param DefinitionProviderInterface $provider The service provider object.
     * @param array                       $options
     */
    protected function registerProvider(DefinitionProviderInterface $provider, $options = [])
    {
        if (in_array($provider, $this->$loadedDefinitions, true)) {
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
        $this->serviceProviderLookup = Arr::add(
            $this->serviceProviderLookup,
            get_class($provider),
            get_class($provider)
        );

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
        $this->$loadedDefinitions[$class] = true;

        $aliases = class_parents($class) + class_implements($class) + [$class];

        foreach ($aliases as $alias) {
            if (!isset($this->$loadedDefinitions[$alias])) {
                $this->$loadedDefinitions[$alias] = $provider;
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

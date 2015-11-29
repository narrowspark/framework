<?php
namespace Viserio\Container\Traits;

use Interop\Container\Definition\DefinitionProviderInterface;
use Viserio\Container\Interfaces\ExtendDefinitionInterface;
use Viserio\Container\Interfaces\ContainerAwareInterface;
use Viserio\Support\Arr;

trait DefinitionProviderTrait
{
    /**
     * @var DefinitionInterface[]
     */
    protected $interopDefinitions = [];

    /**
     * @var ExtendDefinitionInterface[][]
     */
    protected $extensions = [];

    /**
     * Array of all service providers, even those that aren't registered.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * @param DefinitionProviderInterface $provider
     */
    public function addDefinitionProvider(DefinitionProviderInterface $provider)
    {
        foreach ($provider->getDefinitions() as $definition) {
            if ($definition instanceof ExtendDefinitionInterface) {
                $this->extensions[$definition->getExtended()][] = $definition;
            } else {
                $this->interopDefinitions[$definition->getIdentifier()] = $definition;
            }
        }
    }

    /**
     * Get a definition provider in particular.
     *
     * @param string $provider
     *
     * @return DefinitionProviderInterface
     */
    public function getDefinitionProvider($provider)
    {
        return Arr::get($this->definitions, $provider);
    }

    /**
     * Get the definition providers to register.
     *
     * @return DefinitionProviderInterface[]
     */
    public function getDefinitionProviders()
    {
        return $this->definitions;
    }

    /**
     * Set the definition providers to register.
     *
     * @param DefinitionProviderInterface[] $providers
     *
     * @return $this
     */
    public function setDefinitionProviders(array $providers = [])
    {
        $this->definitions = $providers;

        return $this;
    }

    /**
     * Set a definition provider in particular.
     *
     * @param string                      $name
     * @param DefinitionProviderInterface $provider
     *
     * @return $this
     */
    public function setDefinitionProvider($name, $provider)
    {
        $this->definitions[$name] = $provider;

        return $this;
    }
}

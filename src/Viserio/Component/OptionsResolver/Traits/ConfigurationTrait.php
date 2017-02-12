<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use Viserio\Component\OptionsResolver\OptionsResolver;

trait ConfigurationTrait
{
    /**
     * Config array.
     *
     * @var array|\ArrayAccess
     */
    protected $options;

    /**
     * Cache resolver class.
     *
     * @var \Viserio\Component\OptionsResolver\OptionsResolver
     */
    protected $resolvedClass;

    /**
     * Configure and resolve options.
     *
     * @param iterable    $data
     * @param string|null $id
     *
     * @return void
     */
    public function configureOptions(iterable $data, string $id = null): void
    {
        if ($this->resolvedClass === null) {
            if (isset($this->container) && $this->container->has(OptionsResolver::class)) {
                $this->resolvedClass = $this->container->get(OptionsResolver::class);
            } else {
                $this->resolvedClass = new OptionsResolver();
            }
        }

        $this->options = $this->optionsResolver->configure($this, $data)->resolve($id);
    }
}

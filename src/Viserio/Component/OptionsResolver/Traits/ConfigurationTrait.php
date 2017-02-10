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
    protected static $resolvedClass;

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
        if (static::$resolvedClass === null) {
            if (isset($this->container) && $this->container->has(OptionsResolver::class)) {
                static::$resolvedClass = $this->container->get(OptionsResolver::class);
            } else {
                static::$resolvedClass = new OptionsResolver();
            }
        }

        $this->options = static::$optionsResolver->configure($this, $data)->resolve($id);
    }
}

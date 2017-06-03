<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class OptionsResolver
{
    use OptionsResolverTrait;

    /**
     * Configurable class.
     *
     * @var \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    protected $configClass;

    /**
     * Tell the resolver from witch class he should take the configuration.
     *
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     *
     * @return \Viserio\Component\Contracts\OptionsResolver\Resolver
     */
    public function configure(RequiresConfigContract $configClass): self
    {
        $this->configClass = $configClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this->configClass;
    }
}

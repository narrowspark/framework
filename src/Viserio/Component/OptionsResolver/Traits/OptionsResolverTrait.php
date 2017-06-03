<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;

trait OptionsResolverTrait
{
    use AbstractOptionsResolverTrait;

    /**
     * @see \Viserio\Component\OptionsResolver\Traits\AbstractOptionsResolverTrait::getResolvedConfig
     *
     * @param mixed       $config
     * @param null|string $configId
     */
    public function resolveOptions($config, string $configId = null): array
    {
        return self::getResolvedConfig(
            $config,
            $this->getConfigClass(),
            $configId
        );
    }

    /**
     * The configurable class.
     *
     * @return \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    abstract protected function getConfigClass(): RequiresConfigContract;
}

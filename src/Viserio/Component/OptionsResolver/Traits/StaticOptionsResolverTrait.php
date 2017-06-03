<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;

trait StaticOptionsResolverTrait
{
    use AbstractOptionsResolverTrait;

    /**
     * @see \Viserio\Component\OptionsResolver\Traits\AbstractOptionsResolverTrait::getResolvedConfig
     *
     * @param mixed       $config
     * @param null|string $configId
     */
    protected static function resolveOptions($config, string $configId = null): array
    {
        return self::getResolvedConfig(
            $config,
            self::getConfigClass(),
            $configId
        );
    }

    /**
     * The configurable class.
     *
     * @return \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    abstract protected static function getConfigClass(): RequiresConfigContract;
}

<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class StaticOptionsResolver
{
    use StaticOptionsResolverTrait;

    protected static $configClass;

    protected static $data;

    public function configure(RequiresConfigContract $configClass, $data): self
    {
        self::$configClass = $configClass;
        self::$data        = $data;

        return $this;
    }

    public function resolve(string $configId = null): array
    {
        return self::resolveOptions(self::$data, $configId);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return self::$configClass;
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class OptionsResolver
{
    use OptionsResolverTrait;

    protected $configClass;

    protected $data;

    public function configure(RequiresConfigContract $configClass, $data): self
    {
        $this->configClass = $configClass;
        $this->data        = $data;

        return $this;
    }

    public function resolve(string $configId = null): array
    {
        return self::resolveOptions($this->data, $configId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this->configClass;
    }
}

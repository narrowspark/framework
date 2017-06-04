<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class OptionsResolverTraitAndContainerAwareConfiguration implements RequiresComponentConfigContract
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;

    public function getOptions($data)
    {
        $this->resolveOptions($data);

        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }
}

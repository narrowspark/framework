<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\ComponentConfigurationTrait;

class ComponentConfigurationTraitAndContainerAwareConfiguration implements RequiresConfigContract
{
    use ContainerAwareTrait;
    use ComponentConfigurationTrait;

    public function getOptions($data)
    {
        $this->configureOptions($data);

        return $this->options;
    }

    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }
}

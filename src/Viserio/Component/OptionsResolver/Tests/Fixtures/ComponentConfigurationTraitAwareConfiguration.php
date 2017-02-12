<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\OptionsResolver\Traits\ComponentConfigurationTrait;

class ComponentConfigurationTraitAwareConfiguration implements RequiresConfigContract
{
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

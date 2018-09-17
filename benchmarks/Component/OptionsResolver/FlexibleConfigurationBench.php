<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Component\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\FlexibleComponentConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class FlexibleConfigurationBench extends AbstractCase
{
    /**
     * @Subject
     * @Groups({"configId"})
     */
    public function options(): void
    {
        $class = new class() extends FlexibleComponentConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

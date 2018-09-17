<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Component\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ProvidesDefaultOptionsMandatoryBench extends AbstractCase
{
    /**
     * @Subject
     * @Groups({"default", "config", "mandatory"})
     */
    public function options(): void
    {
        $class = new class() extends ConnectionDefaultOptionsWithMandatoryConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Component\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentMandatoryConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class RequiresMandatoryOptionsBench extends AbstractCase
{
    /**
     * @Subject
     * @Groups({"configId", "mandatory"})
     */
    public function options(): void
    {
        $class = new class() extends ConnectionComponentMandatoryConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

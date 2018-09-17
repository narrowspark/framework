<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Component\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentMandatoryContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class RequiresMandatoryOptionsContainerIdBench extends AbstractCase
{
    /**
     * @var bool
     */
    protected $isId = true;

    /**
     * @Subject
     * @Groups({"configId", "mandatory"})
     */
    public function options(): void
    {
        $class = new class() extends ConnectionComponentMandatoryContainerIdConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

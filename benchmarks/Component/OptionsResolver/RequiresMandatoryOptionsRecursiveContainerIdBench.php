<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Component\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentMandatoryRecursiveContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class RequiresMandatoryOptionsRecursiveContainerIdBench extends AbstractCase
{
    /**
     * @var bool
     */
    protected $isId = true;

    /**
     * @Subject
     * @Groups({"configId", "mandatoryRec"})
     */
    public function options(): void
    {
        $class = new class() extends ConnectionComponentMandatoryRecursiveContainerIdConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Component\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionsMandatoryContainedIdConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ProvidesDefaultOptionsMandatoryContainerIdBench extends AbstractCase
{
    /**
     * @var bool
     */
    protected $isId = true;

    /**
     * @Subject
     * @Groups({"default", "config", "mandatory"})
     */
    public function options(): void
    {
        $class = new class() extends ConnectionComponentDefaultOptionsMandatoryContainedIdConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Component\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ProvidesDefaultOptionsBench extends AbstractCase
{
    /**
     * @Subject
     * @Groups({"default", "config"})
     */
    public function options(): void
    {
        $class = new class() extends ConnectionDefaultOptionsConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

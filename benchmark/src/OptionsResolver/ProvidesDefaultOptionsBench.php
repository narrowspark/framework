<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Narrowspark\Benchmark\OptionsResolver;

use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionConfiguration;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ProvidesDefaultOptionsBench extends AbstractCase
{
    /**
     * @Subject
     * @Groups({"default", "config"})
     */
    public function options(): void
    {
        $class = new class() extends ConnectionDefaultOptionConfiguration {
            use OptionsResolverTrait;
        };

        $class::resolveOptions($this->getTestConfig(), $this->configId);
    }
}

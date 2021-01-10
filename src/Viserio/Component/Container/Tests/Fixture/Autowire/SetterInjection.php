<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Tests\Fixture\Autowire;

use Viserio\Component\Container\Tests\Fixture\EmptyClass;

class SetterInjection extends SetterInjectionParent
{
    public function setEmpty(EmptyClass $emptyClass): void
    {
        // should be called
    }

    public function setDependencies(EmptyClass $emptyClass, A $a): void
    {
        // should be called
    }

    /**
     * {@inheritdoc}
     */
    public function setWithCallsConfigured(A $a): void
    {
        // this method has a calls configured on it
    }

    public function notASetter(A $a): void
    {
        // should be called only when explicitly specified
    }

    public function setChildMethodWithoutDocBlock(A $a): void
    {
    }
}

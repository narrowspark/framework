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

namespace Viserio\Component\Container\Tests\Fixture\Autowire;

use Viserio\Component\Container\Tests\Fixture\EmptyClass;

class SetterInjection extends SetterInjectionParent
{
    /**
     * @param EmptyClass $emptyClass
     */
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

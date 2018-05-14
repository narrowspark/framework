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

class SetterInjectionParent
{
    public function setDependencies(EmptyClass $emptyClass, A $a): void
    {
        // should be called
    }

    public function setWithCallsConfigured(A $a): void
    {
    }

    public function setChildMethodWithoutDocBlock(A $a): void
    {
    }
}

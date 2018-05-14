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

namespace Viserio\Contract\Container\Argument;

interface Argument
{
    /**
     * The values in the set.
     *
     * @return \Viserio\Contract\Container\Definition\ReferenceDefinition[]
     */
    public function getValue(): array;

    /**
     * The service references to put in the set.
     *
     * @param \Viserio\Contract\Container\Definition\ReferenceDefinition[] $values
     *
     * @return void
     */
    public function setValue(array $values): void;
}

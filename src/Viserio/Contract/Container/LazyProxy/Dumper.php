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

namespace Viserio\Contract\Container\LazyProxy;

use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;

interface Dumper
{
    /**
     * Inspects whether the given definitions should produce lazy instantiation logic in the dumped container.
     *
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     *
     * @return bool
     */
    public function isSupported(DefinitionContract $definition): bool;

    /**
     * Generates the code for the lazy code.
     *
     * @param \Viserio\Contract\Container\Definition\ObjectDefinition $definition
     *
     * @return string
     */
    public function getProxyCode(ObjectDefinitionContract $definition): string;

    /**
     * Generates the code to be used to instantiate a proxy in the dumped factory code.
     *
     * @param \Viserio\Contract\Container\Definition\ObjectDefinition $definition
     * @param string                                                  $factoryCode The code to execute to create the service
     *
     * @return string
     */
    public function getProxyFactoryCode(ObjectDefinitionContract $definition, string $factoryCode): string;
}

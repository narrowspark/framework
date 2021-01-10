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

namespace Viserio\Contract\Container\LazyProxy;

use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;

interface Dumper
{
    /**
     * Inspects whether the given definitions should produce lazy instantiation logic in the dumped container.
     */
    public function isSupported(DefinitionContract $definition): bool;

    /**
     * Generates the code for the lazy code.
     */
    public function getProxyCode(ObjectDefinitionContract $definition): string;

    /**
     * Generates the code to be used to instantiate a proxy in the dumped factory code.
     *
     * @param string $factoryCode The code to execute to create the service
     */
    public function getProxyFactoryCode(ObjectDefinitionContract $definition, string $factoryCode): string;
}

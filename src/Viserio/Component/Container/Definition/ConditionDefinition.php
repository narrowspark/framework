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

namespace Viserio\Component\Container\Definition;

use Viserio\Component\Container\Definition\Traits\ClassAwareTrait;
use Viserio\Component\Container\Definition\Traits\MethodCallsAwareTrait;
use Viserio\Component\Container\Definition\Traits\PropertiesAwareTrait;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;

/**
 * Only used for the condition callback.
 *
 * @internal
 */
abstract class ConditionDefinition implements MethodCallsAwareDefinitionContract, PropertiesAwareDefinitionContract
{
    use ClassAwareTrait;
    use MethodCallsAwareTrait;
    use PropertiesAwareTrait;
}

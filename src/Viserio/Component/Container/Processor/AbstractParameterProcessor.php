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

namespace Viserio\Component\Container\Processor;

use Viserio\Contract\Container\Processor\ParameterProcessor as ParameterProcessorContract;

abstract class AbstractParameterProcessor implements ParameterProcessorContract
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $parameter): bool
    {
        return \preg_match('/\{(.*)\|(' . \implode('|', \array_keys(static::getProvidedTypes())) . ')\}/', $parameter) === 1;
    }
}

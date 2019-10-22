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

namespace Viserio\Component\Config\ParameterProcessor;

use Viserio\Contract\Config\ParameterProcessor as ParameterProcessorContract;

abstract class AbstractParameterProcessor implements ParameterProcessorContract
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $parameter): bool
    {
        \preg_match('/\%' . static::getReferenceKeyword() . '\:(.*)\%/', $parameter, $matches);

        return \count($matches) !== 0;
    }

    /**
     * Get the value without the reference keyword.
     *
     * @param string $parameter
     *
     * @return string
     */
    protected function parseParameter(string $parameter): string
    {
        \preg_match('/\%' . static::getReferenceKeyword() . '\:(.*)\%/', $parameter, $matches);

        if (\count($matches) !== 0) {
            return $matches[1];
        }

        return $parameter;
    }

    /**
     * Replace parameter key with given value in data string.
     *
     * @param string $data
     * @param string $parameterKey
     * @param string $newValue
     *
     * @return mixed
     */
    protected function replaceData(string $data, string $parameterKey, string $newValue)
    {
        return \str_replace('%' . static::getReferenceKeyword() . ':' . $parameterKey . '%', $newValue, $data);
    }
}

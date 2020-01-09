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
        return \preg_match('/\{[' . \implode('|', \array_keys(static::getProvidedTypes())) . ']+\:(.*)\}/', $parameter) === 1;
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
        \preg_match('/\{[' . \implode('|', \array_keys(static::getProvidedTypes())) . ']+\:(.*)\}/', $parameter, $matches);

        if (\count($matches) !== 0) {
            return $matches[1];
        }

        return $parameter;
    }

    /**
     * Replace parameter key with given value in data string.
     *
     * @param string $prefix
     * @param string $key
     * @param string $data
     * @param string $newValue
     *
     * @return mixed
     */
    protected function replaceData(string $prefix, string $key, string $data, string $newValue)
    {
        return \str_replace('{' . $prefix . ':' . $key . '}', $newValue, $data);
    }
}

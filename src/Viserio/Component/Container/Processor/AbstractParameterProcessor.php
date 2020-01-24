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
    public static function isRuntime(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $parameter): bool
    {
        return \preg_match(\sprintf(static::PROCESSOR_WITH_PLACEHOLDER_REGEX, \implode('|', \array_keys(static::getProvidedTypes()))), $parameter) === 1;
    }

    /**
     * Returns key name, the process that should be used and a replace string.
     *
     * @param string $parameter
     *
     * @return array<int, string>
     */
    protected function getData(string $parameter): array
    {
        [$key, $process] = \explode('|', $parameter);

        \preg_match(self::PARAMETER_REGEX, $key, $match);

        $key = $match[1] ?? $key;

        return [$key, $process, ($match[0] ?? $key) . '|' . $process];
    }
}

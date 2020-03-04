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

namespace Viserio\Component\Container\Processor;

use Viserio\Contract\Container\Exception\InvalidArgumentException;

class EnvParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function isRuntime(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'env' => 'bool|int|float|string|array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key, , $search] = $this->getData($parameter);

        $env = null;

        if (isset($_ENV[$key])) {
            $env = $_ENV[$key];
        } elseif (isset($_SERVER[$key]) && \strpos($key, 'HTTP_') !== 0) {
            $env = $_SERVER[$key];
        } elseif (false !== $getEnv = \getenv($key)) {
            $env = $getEnv;
        }

        if ($env === null) {
            throw new InvalidArgumentException(\sprintf('No env value found for [%s].', $parameter));
        }

        return \str_replace($search, $env, $parameter);
    }
}

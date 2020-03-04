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

namespace Viserio\Contract\Config\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements Exception
{
    /**
     * When an invalid type is encountered.
     *
     * @param string $name        name of the value being validated
     * @param mixed  $provided    the provided value
     * @param array  $expected    the expected value type
     * @param string $configClass
     */
    public static function invalidType(string $name, $provided, array $expected, $configClass): self
    {
        return new self(\sprintf(
            'Invalid configuration value provided for [%s]; Expected [%s], but got [%s], in [%s].',
            $name,
            \count($expected) === 1 ? $expected[0] : \implode('] or [', $expected),
            (\is_object($provided) ? \get_class($provided) : \gettype($provided)),
            $configClass
        ));
    }
}

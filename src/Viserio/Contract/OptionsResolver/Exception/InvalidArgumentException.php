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

namespace Viserio\Contract\OptionsResolver\Exception;

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
     *
     * @return self
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

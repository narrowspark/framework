<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\OptionsResolver\Exception;

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
        if (\count($expected) === 1) {
            $transformedExpect = '[' . $expected[0] . ']';
        } else {
            $transformedExpect = '[' . \implode('] or [', $expected) . ']';
        }

        return new self(\sprintf(
            'Invalid configuration value provided for [%s]; Expected %s, but got [%s], in [%s].',
            $name,
            $transformedExpect,
            (\is_object($provided) ? \get_class($provided) : \gettype($provided)),
            $configClass
        ));
    }
}

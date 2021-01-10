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

namespace Viserio\Component\Container\Argument;

use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class ParameterArgument implements ArgumentContract
{
    /** @var array<int, mixed> */
    private array $values;

    /**
     * Create a new ParameterArgument instance.
     */
    public function __construct(string $parameter, $default)
    {
        if ($parameter === '') {
            throw new InvalidArgumentException('The [$parameter] must be a non-empty string.');
        }

        $this->values = [$parameter, $default];
    }

    /**
     * The values in the set.
     *
     * @return array<int, mixed|string>
     */
    public function getValue(): array
    {
        return $this->values;
    }

    /**
     * Set parameter name as first and default as the second array value.
     *
     * @param mixed[] $values
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException
     */
    public function setValue(array $values): void
    {
        if (\count($values) !== 2 || ! \is_string($values[0])) {
            throw new InvalidArgumentException('A [Viserio\Component\Container\Argument\ParameterArgument] must hold parameter name and default value as fallback if parameter doesn\'t exist.');
        }

        if ($values[0] === '') {
            throw new InvalidArgumentException('The first array value must be a non-empty string.');
        }

        $this->values = $values;
    }
}

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

namespace Viserio\Contract\Finder\Comparator;

interface Comparator
{
    public const DEFAULT_OPERATOR = '==';

    /**
     * Gets the target value.
     *
     * @return int|string The target value
     */
    public function getTarget();

    /**
     * @param int|string $target
     */
    public function setTarget($target): void;

    /**
     * Gets the comparison operator.
     *
     * @return string The operator
     */
    public function getOperator(): string;

    /**
     * Sets the comparison operator.
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException
     */
    public function setOperator(string $operator): void;

    /**
     * Tests against the target.
     *
     * @param mixed $test A test value
     */
    public function test($test): bool;
}

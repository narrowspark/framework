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
     * @param string $operator
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException
     */
    public function setOperator(string $operator): void;

    /**
     * Tests against the target.
     *
     * @param mixed $test A test value
     *
     * @return bool
     */
    public function test($test): bool;
}

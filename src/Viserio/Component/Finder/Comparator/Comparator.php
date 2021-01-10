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

namespace Viserio\Component\Finder\Comparator;

use Viserio\Contract\Finder\Comparator\Comparator as ComparatorContract;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

class Comparator implements ComparatorContract
{
    /** @var int|string */
    private $target;

    /** @var string */
    private $operator = ComparatorContract::DEFAULT_OPERATOR;

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget($target): void
    {
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function setOperator(string $operator): void
    {
        if ($operator === '') {
            $operator = ComparatorContract::DEFAULT_OPERATOR;
        }

        if (! \in_array($operator, ['>', '<', '>=', '<=', '=', '==', '===', '!=', '!==', '!', '<>'], true)) {
            throw new InvalidArgumentException(\sprintf('Invalid operator "%s".', $operator));
        }

        $this->operator = $operator;
    }

    /**
     * {@inheritdoc}
     */
    public function test($test): bool
    {
        switch ($this->operator) {
            case '!':
            case '!=':
            case '<>':
                return ($test <=> $this->target) !== 0;
            case '<=':
                return ($test <=> $this->target) === -1 || ($test <=> $this->target) === 0;
            case '<':
                return ($test <=> $this->target) === -1;
            case '>=':
                return ($test <=> $this->target) === 1 || ($test <=> $this->target) === 0;
            case '>':
                return ($test <=> $this->target) === 1;
            case '===':
                return $test === $this->target;
            case '!==':
                return $test !== $this->target;
            // ==
            default:
                return ($test <=> $this->target) === 0;
        }
    }
}

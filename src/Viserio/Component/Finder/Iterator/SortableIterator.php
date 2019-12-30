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

namespace Viserio\Component\Finder\Iterator;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use SplFileInfo;
use Traversable;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * SortableIterator applies a sort on a given Iterator.
 *
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/SortableIterator.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SortableIterator implements IteratorAggregate
{
    /** @var int */
    public const SORT_BY_NONE = 0;

    /** @var int */
    public const SORT_BY_NAME = 1;

    /** @var int */
    public const SORT_BY_TYPE = 2;

    /** @var int */
    public const SORT_BY_ACCESSED_TIME = 3;

    /** @var int */
    public const SORT_BY_CHANGED_TIME = 4;

    /** @var int */
    public const SORT_BY_MODIFIED_TIME = 5;

    /** @var int */
    public const SORT_BY_NAME_NATURAL = 6;

    /**
     * The Iterator to filter.
     *
     * @var Traversable<int|string, SplFileInfo>
     */
    private $iterator;

    /**
     * The sort type (SORT_BY_NAME, SORT_BY_TYPE, or a PHP callback).
     *
     * @var callable|Closure|int
     */
    private $sort;

    /**
     * Create a new SortableIterator instance.
     *
     * @param Traversable<int|string, SplFileInfo> $iterator
     * @param callable|Closure|int                 $sort         The sort type (SORT_BY_NAME, SORT_BY_TYPE, or a PHP callback)
     * @param bool                                 $reverseOrder
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException
     */
    public function __construct(Traversable $iterator, $sort, bool $reverseOrder = false)
    {
        $this->iterator = $iterator;
        $order = $reverseOrder ? -1 : 1;

        if (self::SORT_BY_NAME === $sort) {
            $this->sort = static function (SplFileInfo $a, SplFileInfo $b) use ($order): int {
                return $order * \strcmp($a->getRealPath() ?: $a->getPathname(), $b->getRealPath() ?: $b->getPathname());
            };
        } elseif (self::SORT_BY_NAME_NATURAL === $sort) {
            $this->sort = static function (SplFileInfo $a, SplFileInfo $b) use ($order): int {
                return $order * \strnatcmp($a->getRealPath() ?: $a->getPathname(), $b->getRealPath() ?: $b->getPathname());
            };
        } elseif (self::SORT_BY_TYPE === $sort) {
            $this->sort = static function (SplFileInfo $a, SplFileInfo $b) use ($order): int {
                if ($a->isDir() && $b->isFile()) {
                    return -$order;
                }

                if ($a->isFile() && $b->isDir()) {
                    return $order;
                }

                return $order * \strcmp($a->getRealPath() ?: $a->getPathname(), $b->getRealPath() ?: $b->getPathname());
            };
        } elseif (self::SORT_BY_ACCESSED_TIME === $sort) {
            $this->sort = static function (SplFileInfo $a, SplFileInfo $b) use ($order): int {
                return $order * ($a->getATime() - $b->getATime());
            };
        } elseif (self::SORT_BY_CHANGED_TIME === $sort) {
            $this->sort = static function (SplFileInfo $a, SplFileInfo $b) use ($order): int {
                return $order * ($a->getCTime() - $b->getCTime());
            };
        } elseif (self::SORT_BY_MODIFIED_TIME === $sort) {
            $this->sort = static function (SplFileInfo $a, SplFileInfo $b) use ($order): int {
                return $order * ($a->getMTime() - $b->getMTime());
            };
        } elseif (self::SORT_BY_NONE === $sort) {
            $this->sort = $order;
        } elseif (\is_callable($sort)) {
            $this->sort = $reverseOrder ? static function (SplFileInfo $a, SplFileInfo $b) use ($sort): int {
                return -$sort($a, $b);
            }
            : $sort;
        } else {
            throw new InvalidArgumentException('The SortableIterator takes a PHP callable or a valid built-in sort algorithm as an argument.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        if ($this->sort === 1) {
            return $this->iterator;
        }

        $array = \iterator_to_array($this->iterator, true);

        if ($this->sort === -1) {
            $array = \array_reverse($array);
        } else {
            \uasort($array, $this->sort);
        }

        return new ArrayIterator($array);
    }
}

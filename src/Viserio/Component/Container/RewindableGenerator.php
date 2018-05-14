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

namespace Viserio\Component\Container;

use Countable;
use IteratorAggregate;
use Traversable;

class RewindableGenerator implements Countable, IteratorAggregate
{
    /**
     * The generator callback.
     *
     * @var callable
     */
    protected $generator;

    /**
     * The number of tagged services.
     *
     * @var callable|int
     */
    protected $count;

    /**
     * Create a new generator instance.
     *
     * @param callable     $generator
     * @param callable|int $count
     *
     * @return void
     */
    public function __construct(callable $generator, $count)
    {
        $this->count = $count;
        $this->generator = $generator;
    }

    /**
     * Get the total number of tagged services.
     *
     * @return int
     */
    public function count(): int
    {
        $count = $this->count;

        if (\is_callable($count)) {
            $count = $this->count = $count();
        }

        return $count;
    }

    /**
     * Get an iterator from the generator.
     *
     * @return \Traversable
     */
    public function getIterator(): Traversable
    {
        return ($this->generator)();
    }
}

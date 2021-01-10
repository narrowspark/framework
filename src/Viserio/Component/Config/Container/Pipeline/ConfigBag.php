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

namespace Viserio\Component\Config\Container\Pipeline;

use ArrayAccess;
use Countable;
use Viserio\Contract\Config\Exception\LogicException;

class ConfigBag implements ArrayAccess, Countable
{
    /** @var array<int|string, mixed> */
    private array $data;

    public function __construct(iterable $data, ?array $merge = null)
    {
        $this->data = (array) $data;

        if ($merge !== null) {
            $this->data = \array_merge_recursive($this->data, $merge);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return \array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Impossible to call offsetSet() on a frozen ConfigBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Impossible to call offsetUnset() on a frozen ConfigBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return \count($this->data);
    }
}

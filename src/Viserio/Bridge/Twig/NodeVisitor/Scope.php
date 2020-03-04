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

namespace Viserio\Bridge\Twig\NodeVisitor;

use LogicException;

/**
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 */
class Scope
{
    /** @var self */
    private $parent;

    /** @var array */
    private $data = [];

    /** @var bool */
    private $left = false;

    /**
     * Create a new Scope instance.
     */
    public function __construct(?self $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Opens a new child scope.
     */
    public function enter(): self
    {
        return new self($this);
    }

    /**
     * Closes current scope and returns parent one.
     */
    public function leave(): ?self
    {
        $this->left = true;

        return $this->parent;
    }

    /**
     * Stores data into current scope.
     *
     * @throws LogicException
     *
     * @return $this
     */
    public function set(string $key, $value)
    {
        if ($this->left) {
            throw new LogicException('Left scope is not mutable.');
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Tests if a data is visible from current scope.
     */
    public function has(string $key): bool
    {
        if (\array_key_exists($key, $this->data)) {
            return true;
        }

        if ($this->parent === null) {
            return false;
        }

        return $this->parent->has($key);
    }

    /**
     * Returns data visible from current scope.
     */
    public function get(string $key, $default = null)
    {
        if (\array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if ($this->parent === null) {
            return $default;
        }

        return $this->parent->get($key, $default);
    }
}

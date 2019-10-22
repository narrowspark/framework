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

namespace Viserio\Bridge\Twig\NodeVisitor;

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
     *
     * @param null|self $parent
     */
    public function __construct(self $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Opens a new child scope.
     *
     * @return self
     */
    public function enter(): self
    {
        return new self($this);
    }

    /**
     * Closes current scope and returns parent one.
     *
     * @return null|self
     */
    public function leave(): ?self
    {
        $this->left = true;

        return $this->parent;
    }

    /**
     * Stores data into current scope.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function set(string $key, $value)
    {
        if ($this->left) {
            throw new \LogicException('Left scope is not mutable.');
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Tests if a data is visible from current scope.
     *
     * @param string $key
     *
     * @return bool
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
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
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

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

namespace Viserio\Contract\Container\Definition;

use Viserio\Contract\Container\Argument\ConditionArgument;

interface Definition extends ChangeAwareDefinition, DeprecatedDefinition
{
    /** @var int */
    public const SERVICE = 1;

    /** @var int */
    public const SINGLETON = 2;

    /** @var int */
    public const PRIVATE = 3;

    /**
     * Get the definition hash.
     */
    public function getHash(): string;

    /**
     * Set the binding name.
     *
     * @return static
     */
    public function setName(string $id);

    /**
     * Get the binding name.
     */
    public function getName(): string;

    /**
     * Check if the binding is lazy.
     */
    public function isLazy(): bool;

    /**
     * Check if the binding is public.
     *
     * - synthetic services are always public.
     */
    public function isPublic(): bool;

    /**
     * Check if the binding is shared.
     */
    public function isShared(): bool;

    /**
     * Set the binding lazy.
     *
     * @return static
     */
    public function setLazy(bool $bool);

    /**
     * Set the binding public.
     *
     * @return static
     */
    public function setPublic(bool $bool);

    /**
     * Get the service value.
     */
    public function getValue();

    /**
     * Set a new value for the definition.
     *
     * @return static
     */
    public function setValue($value);

    /**
     * Whether this definition is synthetic, that is not constructed by the
     * container, but dynamically injected.
     */
    public function isSynthetic(): bool;

    /**
     * Sets whether this definition is synthetic, that is not constructed by the
     * container, but dynamically injected.
     *
     * @return static
     */
    public function setSynthetic(bool $boolean);

    /**
     * Get added definition conditions.
     *
     * @return \Viserio\Contract\Container\Argument\ConditionArgument[]
     */
    public function getConditions(): array;

    /**
     * Set a definition conditions.
     *
     * @param \Viserio\Contract\Container\Argument\ConditionArgument[] $conditions
     *
     * @return static
     */
    public function setConditions(array $conditions);

    /**
     * @return static
     */
    public function addCondition(ConditionArgument $condition);

    /**
     * Get type of definition.
     */
    public function getType(): int;

    /**
     * Set type of definition.
     */
    public function setType(int $type): void;
}

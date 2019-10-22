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
     *
     * @return string
     */
    public function getHash(): string;

    /**
     * Set the binding name.
     *
     * @param string $id
     *
     * @return static
     */
    public function setName(string $id);

    /**
     * Get the binding name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the binding is lazy.
     *
     * @return bool
     */
    public function isLazy(): bool;

    /**
     * Check if the binding is public.
     *
     * - synthetic services are always public.
     *
     * @return bool
     */
    public function isPublic(): bool;

    /**
     * Check if the binding is shared.
     *
     * @return bool
     */
    public function isShared(): bool;

    /**
     * Set the binding lazy.
     *
     * @param bool $bool
     *
     * @return static
     */
    public function setLazy(bool $bool);

    /**
     * Set the binding public.
     *
     * @param bool $bool
     *
     * @return static
     */
    public function setPublic(bool $bool);

    /**
     * Get the service value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set a new value for the definition.
     *
     * @param mixed $value
     *
     * @return static
     */
    public function setValue($value);

    /**
     * Whether this definition is synthetic, that is not constructed by the
     * container, but dynamically injected.
     *
     * @return bool
     */
    public function isSynthetic(): bool;

    /**
     * Sets whether this definition is synthetic, that is not constructed by the
     * container, but dynamically injected.
     *
     * @param bool $boolean
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
     * @param \Viserio\Contract\Container\Argument\ConditionArgument $condition
     *
     * @return static
     */
    public function addCondition(ConditionArgument $condition);

    /**
     * Get type of definition.
     *
     * @return int
     */
    public function getType(): int;

    /**
     * Set type of definition.
     *
     * @param int $type
     *
     * @return void
     */
    public function setType(int $type): void;
}

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

namespace Viserio\Component\Container\Definition;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\Traits\ChangesAwareTrait;
use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Contract\Container\Argument\ConditionArgument as ConditionArgumentContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\TagAwareDefinition as TagAwareDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
abstract class AbstractDefinition implements DefinitionContract, TagAwareDefinitionContract
{
    use DeprecationTrait;
    use ChangesAwareTrait;

    /**
     * The service name.
     */
    protected string $name;

    /**
     * The hash of this definition.
     */
    protected string $hash;

    /**
     * The service value.
     */
    protected $value;

    /**
     * Check if the service is lazy.
     */
    protected bool $isLazy = false;

    /**
     * Check if the service is public.
     */
    protected bool $isPublic = false;

    /**
     * Check if the service is a internal php class or function.
     */
    protected bool $isInternal = false;

    /**
     * The service type.
     */
    protected int $type;

    /**
     * Check if the value is added on runtime.
     */
    protected bool $synthetic = false;

    /**
     * Returns the list of tags.
     */
    protected array $tags = [];

    /**
     * List of definition conditions.
     */
    protected array $conditions = [];

    /**
     * Extend this class to create new Definitions.
     */
    public function __construct(string $name, int $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->hash = ContainerBuilder::getHash($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $id)
    {
        $this->name = $id;

        return $this;
    }

    /**
     * Get the definition hash.
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLazy(): bool
    {
        return $this->isLazy;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublic(): bool
    {
        return $this->synthetic !== false ? $this->synthetic : $this->isPublic;
    }

    /**
     * Check if the service is is a internal php class or function.
     *
     * @internal
     */
    public function isInternal(): bool
    {
        return $this->isInternal;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function isSynthetic(): bool
    {
        return $this->synthetic;
    }

    /**
     * {@inheritdoc}
     */
    public function setSynthetic(bool $boolean)
    {
        $this->synthetic = $boolean;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags(array $tags)
    {
        $this->tags = [];

        $this->changes['tags'] = true;

        foreach ($tags as $name => $attributes) {
            if ($name === '') {
                throw new InvalidArgumentException('The tag name cant be a empty string.');
            }

            $this->tags[$name] = $attributes;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = [];

        foreach ($conditions as $condition) {
            $this->addCondition($condition);
        }

        return $this;
    }

    /**
     * Set the service internal.
     *
     * @internal
     *
     * @return static
     */
    public function setInternal(bool $bool)
    {
        $this->isInternal = $bool;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isShared(): bool
    {
        return $this->type === 2 /* DefinitionContract::SINGLETON */ || $this->type === 5 /* DefinitionContract::SINGLETON + DefinitionContract::PRIVATE */;
    }

    /**
     * {@inheritdoc}
     */
    public function setLazy(bool $bool)
    {
        $this->isLazy = $bool;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublic(bool $bool)
    {
        $this->isPublic = $bool;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTag(string $name): array
    {
        return $this->tags[$name] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function addTag(string $name, array $attributes = [])
    {
        if ($name === '') {
            throw new InvalidArgumentException('The tag name cant be a empty string.');
        }

        $this->changes['tags'] = true;

        $this->tags[$name][] = $attributes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTag(string $name): bool
    {
        return \array_key_exists($name, $this->tags);
    }

    /**
     * {@inheritdoc}
     */
    public function clearTag(string $name)
    {
        unset($this->tags[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearTags()
    {
        $this->tags = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCondition(ConditionArgumentContract $condition): DefinitionContract
    {
        $this->conditions[] = $condition;

        $this->changes['condition'] = true;

        return $this;
    }
}

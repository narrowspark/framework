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
     *
     * @var string
     */
    protected $name;

    /**
     * The hash of this definition.
     *
     * @var string
     */
    protected $hash;

    /**
     * The service value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Check if the service is lazy.
     *
     * @var bool
     */
    protected $isLazy = false;

    /**
     * Check if the service is public.
     *
     * @var bool
     */
    protected $isPublic = false;

    /**
     * Check if the service is a internal php class or function.
     *
     * @var bool
     */
    protected $isInternal = false;

    /**
     * The service type.
     *
     * @var int
     */
    protected $type;

    /**
     * Check if the value is added on runtime.
     *
     * @var bool
     */
    protected $synthetic = false;

    /**
     * Returns the list of tags.
     *
     * @var array
     */
    protected $tags = [];

    /**
     * List of definition conditions.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * Extend this class to create new Definitions.
     *
     * @param string $name
     * @param int    $type
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
    public function setName(string $id): DefinitionContract
    {
        $this->name = $id;

        return $this;
    }

    /**
     * Get the definition hash.
     *
     * @return string
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
    public function setValue($value): DefinitionContract
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
     *
     * @return bool
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
    public function setSynthetic(bool $boolean): DefinitionContract
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
    public function setConditions(array $conditions): DefinitionContract
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
     * @param bool $bool
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
    public function setLazy(bool $bool): DefinitionContract
    {
        $this->isLazy = $bool;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublic(bool $bool): DefinitionContract
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

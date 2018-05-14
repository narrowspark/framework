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
use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Contract\Container\Definition\AliasDefinition as AliasDefinitionContract;

final class AliasDefinition implements AliasDefinitionContract
{
    use DeprecationTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service alias is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * The hash of this definition.
     *
     * @var string
     */
    protected $hash;

    /**
     * The definition name.
     *
     * @var string
     */
    private $original;

    /**
     * The alias name.
     *
     * @var string
     */
    private $name;

    /**
     * Check if the alias is public.
     *
     * @var bool
     */
    private $isPublic = false;

    /**
     * Create a new AliasDefinition instance.
     *
     * @param string $original
     * @param string $alias
     */
    public function __construct(string $original, string $alias)
    {
        $this->original = $original;
        $this->name = $alias;
        $this->hash = ContainerBuilder::getHash($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->original;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $original): void
    {
        $this->original = $original;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * {@inheritdoc}
     */
    public function setAlias(string $name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublic(bool $bool): AliasDefinitionContract
    {
        $this->isPublic = $bool;

        return $this;
    }
}

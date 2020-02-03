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

namespace Viserio\Component\Container\Pipeline;

use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\DeprecatedDefinition as DeprecatedDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Definition\TagAwareDefinition as TagAwareDefinitionContract;

class ResolvePreloadPipe extends AbstractRecursivePipe
{
    /** @var string */
    public const TAG = 'viserio.container.preload';

    private string $tagName;

    private array $resolvedIds = [];

    /**
     * Create a new ResolvePreloadPipe instance.
     *
     * @param string $tagName
     */
    public function __construct(string $tagName = self::TAG)
    {
        $this->tagName = $tagName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        try {
            parent::process($containerBuilder);
        } finally {
            $this->resolvedIds = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof ArgumentContract) {
            return $value;
        }

        if ($value instanceof TagAwareDefinitionContract && $value instanceof DeprecatedDefinitionContract && $isRoot && (\array_key_exists($this->currentId, $this->resolvedIds) || ! $value->hasTag($this->tagName) || $value->isDeprecated())) {
            return $value->isDeprecated() ? $value->clearTag($this->tagName) : $value;
        }

        if ($value instanceof ReferenceDefinitionContract && $value->getBehavior() !== 3/* ReferenceDefinitionContract::IGNORE_ON_UNINITIALIZED_REFERENCE */ && $this->containerBuilder->has($id = $value->getName())) {
            /** @var \Viserio\Contract\Container\Definition\TagAwareDefinition&\Viserio\Contract\Container\Definition\DeprecatedDefinition $definition */
            $definition = $this->containerBuilder->findDefinition($id);

            if (! $definition->hasTag($this->tagName) && ! $definition->isDeprecated()) {
                $this->resolvedIds[$id] = true;

                $definition->addTag($this->tagName);

                parent::processValue($definition, false);
            }

            return $value;
        }

        return parent::processValue($value, $isRoot);
    }
}

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

use Viserio\Component\Container\Pipeline\AnalyzeServiceDependenciesPipe;
use Viserio\Component\Container\Pipeline\AutowireArrayParameterCompilerPipe;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Pipeline\CheckArgumentsValidityPipe;
use Viserio\Component\Container\Pipeline\CheckCircularReferencesPipe;
use Viserio\Component\Container\Pipeline\CheckDefinitionConditionsPipe;
use Viserio\Component\Container\Pipeline\DecoratorServicePipe;
use Viserio\Component\Container\Pipeline\ExtendedDefinitionPipe;
use Viserio\Component\Container\Pipeline\InlineServiceDefinitionsPipe;
use Viserio\Component\Container\Pipeline\RemovePrivateAliasesPipe;
use Viserio\Component\Container\Pipeline\RemoveUninitializedReferencesInMethodCallsPipe;
use Viserio\Component\Container\Pipeline\RemoveUnusedDefinitionsPipe;
use Viserio\Component\Container\Pipeline\ReplaceAliasByActualDefinitionPipe;
use Viserio\Component\Container\Pipeline\ReplaceDefinitionTypeToPrivateIfReferenceExistsPipe;
use Viserio\Component\Container\Pipeline\ResolveFactoryClassPipe;
use Viserio\Component\Container\Pipeline\ResolveInvalidReferencesPipe;
use Viserio\Component\Container\Pipeline\ResolveParameterPlaceHoldersPipe;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Component\Container\Pipeline\ResolveReferenceAliasesToDependencyReferencesPipe;
use Viserio\Component\Container\Pipeline\ResolveUndefinedDefinitionPipe;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Pipe as PipeContract;

final class PipelineConfig
{
    /** @var string */
    public const TYPE_AFTER_REMOVING = 'afterRemoving';

    /** @var string */
    public const TYPE_BEFORE_OPTIMIZATION = 'beforeOptimization';

    /** @var string */
    public const TYPE_BEFORE_REMOVING = 'beforeRemoving';

    /** @var string */
    public const TYPE_OPTIMIZE = 'optimization';

    /** @var string */
    public const TYPE_REMOVE = 'removing';

    /**
     * List of the after removing pipelines.
     *
     * @var array
     */
    private $afterRemovingPipelines = [];

    /**
     * List of the before optimization pipelines.
     *
     * @var array
     */
    private $beforeOptimizationPipelines = [];

    /**
     * List of the before removing pipelines.
     *
     * @var array
     */
    private $beforeRemovingPipelines = [];

    /**
     * List of the optimization pipelines.
     *
     * @var array
     */
    private $optimizationPipelines = [];

    /**
     * List of the removing pipelines.
     *
     * @var array
     */
    private $removingPipelines = [];

    /**
     * Create a new Pipeline Config instance.
     */
    public function __construct()
    {
        $this->beforeOptimizationPipelines = [
            32 => [
                new ExtendedDefinitionPipe(),
                new ResolveParameterPlaceHoldersPipe(),
                new ResolveUndefinedDefinitionPipe(),
            ],
        ];
        $this->optimizationPipelines = [[
            new CheckDefinitionConditionsPipe(),
            new ResolveFactoryClassPipe(),
            new DecoratorServicePipe(),
            new AutowirePipe(),
            new AutowireArrayParameterCompilerPipe(),
            new ResolveReferenceAliasesToDependencyReferencesPipe(),
            new ResolveInvalidReferencesPipe(),
            new AnalyzeServiceDependenciesPipe(true),
            new CheckCircularReferencesPipe(),
            new CheckArgumentsValidityPipe(),
        ]];
        $this->removingPipelines = [[
            new RemovePrivateAliasesPipe(),
            new ReplaceAliasByActualDefinitionPipe(),
            new RemoveUnusedDefinitionsPipe(),
            new RemoveUninitializedReferencesInMethodCallsPipe(),
            new InlineServiceDefinitionsPipe(new AnalyzeServiceDependenciesPipe()),
            new ReplaceDefinitionTypeToPrivateIfReferenceExistsPipe(),
            new AnalyzeServiceDependenciesPipe(),
        ]];
        $this->afterRemovingPipelines = [[
            new ResolvePreloadPipe(),
        ]];
    }

    /**
     * Gets all pipelines for the AfterRemoving pipeline.
     *
     * @return \Viserio\Contract\Container\Pipe[]
     */
    public function getAfterRemovingPipelines(): array
    {
        return $this->sortPipelines($this->afterRemovingPipelines);
    }

    /**
     * Sets the AfterRemoving pipelines.
     *
     * @param \Viserio\Contract\Container\Pipe[] $pipelines
     *
     * @return void
     */
    public function setAfterRemovingPipelines(array $pipelines): void
    {
        $this->afterRemovingPipelines = [$pipelines];
    }

    /**
     * Gets all pipelines for the BeforeOptimization pipeline.
     *
     * @return \Viserio\Contract\Container\Pipe[]
     */
    public function getBeforeOptimizationPipelines(): array
    {
        return $this->sortPipelines($this->beforeOptimizationPipelines);
    }

    /**
     * Sets the BeforeOptimization pipelines.
     *
     * @param \Viserio\Contract\Container\Pipe[] $pipelines
     *
     * @return void
     */
    public function setBeforeOptimizationPipelines(array $pipelines): void
    {
        $this->beforeOptimizationPipelines = [$pipelines];
    }

    /**
     * Gets all pipelines for the BeforeRemoving pipeline.
     *
     * @return \Viserio\Contract\Container\Pipe[]
     */
    public function getBeforeRemovingPipelines(): array
    {
        return $this->sortPipelines($this->beforeRemovingPipelines);
    }

    /**
     * Sets the BeforeRemoving pipelines.
     *
     * @param \Viserio\Contract\Container\Pipe[] $pipelines
     *
     * @return void
     */
    public function setBeforeRemovingPipelines(array $pipelines): void
    {
        $this->beforeRemovingPipelines = [$pipelines];
    }

    /**
     * Gets all pipelines for the Optimization pipeline.
     *
     * @return \Viserio\Contract\Container\Pipe[]
     */
    public function getOptimizationPipelines(): array
    {
        return $this->sortPipelines($this->optimizationPipelines);
    }

    /**
     * Sets the Optimization pipelines.
     *
     * @param \Viserio\Contract\Container\Pipe[] $pipelines
     *
     * @return void
     */
    public function setOptimizationPipelines(array $pipelines): void
    {
        $this->optimizationPipelines = [$pipelines];
    }

    /**
     * Gets all pipelines for the Removing pipeline.
     *
     * @return \Viserio\Contract\Container\Pipe[]
     */
    public function getRemovingPipelines(): array
    {
        return $this->sortPipelines($this->removingPipelines);
    }

    /**
     * Sets the Removing pipelines.
     *
     * @param \Viserio\Contract\Container\Pipe[] $pipelines
     *
     * @return void
     */
    public function setRemovingPipelines(array $pipelines): void
    {
        $this->removingPipelines = [$pipelines];
    }

    /**
     * Adds a pipeline.
     *
     * @param \Viserio\Contract\Container\Pipe $pipeline A Compiler pipeline
     * @param string                           $type     The pipeline type
     * @param int                              $priority Used to sort the pipelines
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException when a pipeline type doesn't exist
     *
     * @return void
     */
    public function addPipe(
        PipeContract $pipeline,
        string $type = self::TYPE_BEFORE_OPTIMIZATION,
        int $priority = 0
    ): void {
        if ($type === self::TYPE_AFTER_REMOVING) {
            $pipelines = &$this->afterRemovingPipelines;
        } elseif ($type === self::TYPE_BEFORE_REMOVING) {
            $pipelines = &$this->beforeRemovingPipelines;
        } elseif ($type === self::TYPE_REMOVE) {
            $pipelines = &$this->removingPipelines;
        } elseif ($type === self::TYPE_OPTIMIZE) {
            $pipelines = &$this->optimizationPipelines;
        } elseif ($type === self::TYPE_BEFORE_OPTIMIZATION) {
            $pipelines = &$this->beforeOptimizationPipelines;
        } else {
            throw new InvalidArgumentException(\sprintf('Invalid type [%s].', $type));
        }

        if (! \array_key_exists($priority, $pipelines)) {
            $pipelines[$priority] = [];
        }

        $pipelines[$priority][] = $pipeline;
    }

    /**
     * Returns all pipelines in order to be processed.
     *
     * @internal
     *
     * @return \Viserio\Contract\Container\Pipe[]
     */
    public function getPipelines(): array
    {
        return \array_merge(
            $this->getBeforeOptimizationPipelines(),
            $this->getOptimizationPipelines(),
            $this->getBeforeRemovingPipelines(),
            $this->getRemovingPipelines(),
            $this->getAfterRemovingPipelines()
        );
    }

    /**
     * Sort pipelines by priority.
     *
     * @param array $pipelines
     *
     * @return \Viserio\Contract\Container\Pipe[]
     */
    private function sortPipelines(array $pipelines): array
    {
        if (0 === \count($pipelines)) {
            return [];
        }

        \krsort($pipelines);

        // Flatten the array
        return \array_merge(...$pipelines);
    }
}

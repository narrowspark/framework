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

namespace Viserio\Provider\Framework\Container\Processor;

use Viserio\Component\Container\Processor\AbstractParameterProcessor;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class DirectoryParameterProcessor extends AbstractParameterProcessor implements RequiresComponentConfigContract,
    RequiresMandatoryConfigContract,
    RequiresValidatedConfigContract
{
    public const PARAMETER_KEY = 'viserio.app.directory.processor.check_strict';

    /** @var array<string, array<int, string>> */
    protected array $mappers;

    private CompiledContainerContract $compiledContainer;

    /**
     * Check to active or deactivate strict parameter resolving.
     *
     * @var bool
     */
    private bool $strict;

    /**
     * Create a new DirectoryParameterProcessor instance.
     *
     * @param array<string, array<int, string>>             $mappers
     * @param \Viserio\Contract\Container\CompiledContainer $compiledContainer
     */
    public function __construct(array $mappers, CompiledContainerContract $compiledContainer)
    {
        $this->compiledContainer = $compiledContainer;
        $this->mappers = $mappers;

        if ($compiledContainer->hasParameter($key = 'viserio.app.directory.processor.check_strict')) {
            $this->strict = $compiledContainer->getParameter($key);
        } else {
            $this->strict = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'app', 'directory'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'mapper',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigValidators(): iterable
    {
        return [
            'mapper' => ['array'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function isRuntime(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'directory' => 'string',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key,, $search] = $this->getData($parameter);

        $value = $this->mappers[$key] ?? null;

        if ($value === null) {
            if ($this->strict) {
                throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, no mapper was found.', $parameter));
            }

            return $parameter;
        }

        $newValue = $this->compiledContainer->hasParameter($value[0]) ? $this->compiledContainer->getParameter($value[0])->{$value[1]}() : null;

        if ($newValue === null) {
            $newValue = $this->compiledContainer->has($value[0]) ? $this->compiledContainer->get($value[0])->{$value[1]}() : null;
        }

        if ($this->strict && $newValue === null) {
            throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, mapper [%s::%s] was not found.', $parameter, $value[0], $value[1]));
        }

        if ($newValue === null) {
            return $parameter;
        }

        return \str_replace($search, $newValue, $parameter);
    }
}

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

namespace Viserio\Component\Container\Processor;

use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\ParameterNotFoundException;
use Viserio\Contract\Container\Exception\RuntimeException;

class ResolveRuntimeParameterProcessor extends AbstractParameterProcessor
{
    /**
     * A compiled container instance.
     *
     * @var null|\Viserio\Contract\Container\CompiledContainer
     */
    protected $container;

    /**
     * Create a new ResolveRuntimeParameterProcessor instance.
     *
     * @return static
     */
    public function __construct(CompiledContainerContract $container)
    {
        $this->container = $container;
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
        return ['resolve' => 'string'];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key,, $search] = $this->getData($parameter);

        $value = \array_reduce(
            \explode('.', $key),
            static function ($value, $key) {
                return $value[$key] ?? null;
            },
            $this->container->getParameters()
        );

        if ($value === null) {
            try {
                $value = $this->container->getParameter($key);
            } catch (ParameterNotFoundException $exception) {
                throw new InvalidArgumentException(\sprintf('The dynamic parameter [%s] must be defined.', $key));
            }
        }

        if (! \is_scalar($value)) {
            throw new RuntimeException(\sprintf('Parameter [%s] found when resolving [%s] must be scalar, [%s] given.', $key, $parameter, \gettype($value)));
        }

        return \str_replace($search, $value, $parameter);
    }
}

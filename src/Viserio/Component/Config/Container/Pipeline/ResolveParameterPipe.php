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

namespace Viserio\Component\Config\Container\Pipeline;

use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Pipe as PipeContract;

class ResolveParameterPipe implements PipeContract
{
    /** @var array<int, \Viserio\Contract\Config\Processor\ParameterProcessor> */
    protected $processors;

    /**
     * Create a new ResolveParameterPipe instance.
     *
     * @param null|array<int, \Viserio\Contract\Config\Processor\ParameterProcessor> $processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $parameters = $containerBuilder->getParameters();

        foreach ($parameters as $definition) {
            $value = $definition->getValue();

            if (\is_array($value)) {
                $value = $this->processParameters($value);
            } else {
                $value = $this->processParameter($value);
            }

            $definition->setValue($value);

            $name = $definition->getName();

            foreach ($this->processors as $processor) {
                if ($processor->supports($name)) {
                    $name = $processor->process($name);
                }
            }

            $definition->setName($name);

            $parameters[$name] = $definition;
        }

        $containerBuilder->setParameters($parameters);
    }

    /**
     * Process through all parameter processors.
     *
     * @param array $data
     *
     * @return array
     */
    private function processParameters(array $data): array
    {
        \array_walk_recursive($data, function (&$parameter): void {
            if (\is_array($parameter)) {
                $parameter = $this->processParameters($parameter);
            } else {
                $parameter = $this->processParameter($parameter);
            }
        });

        return $data;
    }

    /**
     * Process through definition value.
     *
     * @param int|string $value
     *
     * @return int|string
     */
    private function processParameter($value)
    {
        if (\is_string($value)) {
            /** @var \Viserio\Contract\Config\Processor\ParameterProcessor $processor */
            foreach ($this->processors as $processor) {
                if ($processor->supports($value)) {
                    $value = $processor->process($value);
                }
            }
        }

        return $value;
    }
}

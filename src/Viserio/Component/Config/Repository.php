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

namespace Viserio\Component\Config;

use ArrayIterator;
use IteratorAggregate;
use Narrowspark\Arr\Arr;
use Viserio\Contract\Config\Exception\FileNotFoundException;
use Viserio\Contract\Config\ParameterProcessor as ParameterProcessorContract;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Parser\Traits\ParserAwareTrait;

class Repository implements IteratorAggregate, RepositoryContract
{
    use ParserAwareTrait;

    /**
     * Config folder path.
     *
     * @var string
     */
    protected $path;

    /**
     * Storage array of values.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Array of all processors.
     *
     * @var \Viserio\Contract\Config\ParameterProcessor[]
     */
    protected $parameterProcessors = [];

    /**
     * {@inheritdoc}
     */
    public function getParameterProcessors(): array
    {
        return $this->parameterProcessors;
    }

    /**
     * {@inheritdoc}
     */
    public function addParameterProcessor(ParameterProcessorContract $parameterProcessor): RepositoryContract
    {
        $this->parameterProcessors[$parameterProcessor::getReferenceKeyword()] = $parameterProcessor;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function import(string $filePath, array $options = null): RepositoryContract
    {
        if ($this->loader === null && \pathinfo($filePath, \PATHINFO_EXTENSION) === 'php') {
            if (! \file_exists($filePath)) {
                throw new FileNotFoundException(\sprintf('File [%s] not found.', $filePath));
            }

            $config = (array) require \str_replace(['\\', '/'], '/', $filePath);
        } else {
            $config = $this->loader->load($filePath, $options);
        }

        $this->setArray($config);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): RepositoryContract
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        if (! $this->offsetExists($key)) {
            return $default;
        }

        return $this->offsetGet($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): RepositoryContract
    {
        $this->offsetUnset($key);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setArray(array $values = []): RepositoryContract
    {
        $this->data = Arr::merge($this->data, $values);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllProcessed(): array
    {
        return $this->processParameters($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllFlat(): array
    {
        return Arr::flatten($this->data, '.');
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys(): array
    {
        return \array_keys(Arr::flatten($this->data, '.'));
    }

    /**
     * Get a value from a nested array based on a separated key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        $value = Arr::get($this->data, $key);

        if (\is_array($value)) {
            $value = $this->processParameters($value);
        } else {
            $value = $this->processParameter($value);
        }

        return $value;
    }

    /**
     * Set nested array values based on a separated key.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function offsetSet($key, $value): self
    {
        $this->data = Arr::set($this->data, $key, $value);

        return $this;
    }

    /**
     * Check an array has a value based on a separated key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Remove nested array value based on a separated key.
     *
     * @param string $key
     *
     * @return \Viserio\Contract\Config\Repository
     */
    public function offsetUnset($key): RepositoryContract
    {
        Arr::forget($this->data, $key);

        return $this;
    }

    /**
     * Get an ArrayIterator for the stored items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getAll());
    }

    /**
     * Process array through all parameter processors.
     *
     * @param array $data
     *
     * @return array
     */
    private function processParameters(array $data): array
    {
        \array_walk_recursive($data, function (&$parameter): void {
            /** @codeCoverageIgnoreStart */
            if (\is_array($parameter)) {
                $parameter = $this->processParameters($parameter);
            /** @codeCoverageIgnoreEnd */
            } else {
                $parameter = $this->processParameter($parameter);
            }
        });

        return $data;
    }

    /**
     * Process through all parameter processors.
     *
     * @param mixed $parameter
     *
     * @return mixed
     */
    private function processParameter($parameter)
    {
        foreach ($this->parameterProcessors as $processor) {
            if ($processor->supports((string) $parameter)) {
                return $processor->process($parameter);
            }
        }

        return $parameter;
    }
}

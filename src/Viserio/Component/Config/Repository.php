<?php
declare(strict_types=1);
namespace Viserio\Component\Config;

use ArrayIterator;
use IteratorAggregate;
use Narrowspark\Arr\Arr;
use Viserio\Component\Contract\Config\Exception\FileNotFoundException;
use Viserio\Component\Contract\Config\ParameterProcessor as ParameterProcessorContract;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Parser\Traits\ParserAwareTrait;

class Repository implements RepositoryContract, IteratorAggregate
{
    use ParserAwareTrait;

    /**
     * Config folder path.
     *
     * @var string
     */
    protected $path;

    /**
     * Cache of previously parsed keys.
     *
     * @var array
     */
    protected $keys = [];

    /**
     * Storage array of values.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Array of all processors.
     *
     * @var \Viserio\Component\Contract\Config\ParameterProcessor[]
     */
    protected $parameterProcessors = [];

    /**
     * {@inheritdoc}
     */
    public function addParameterProcessor(ParameterProcessorContract $parameterProcessor): RepositoryContract
    {
        $this->parameterProcessors[\get_class($parameterProcessor)] = $parameterProcessor;

        return $this;
    }

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
    public function import(string $filePath, array $options = null): RepositoryContract
    {
        if ($this->loader === null && \pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
            if (! \file_exists($filePath)) {
                throw new FileNotFoundException(\sprintf('File [%s] not found.', $filePath));
            }

            $config = (array) require \str_replace('\\', '/', $filePath);
        } else {
            $config = $this->getLoader()->load($filePath, $options);
        }

        $this->setArray($config);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): RepositoryContract
    {
        $this->offsetSet($key, $this->processParameter($value));

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
        $this->data = Arr::merge($this->data, $this->processParameters($values));

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
        return Arr::get($this->data, $key);
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
     * @return \Viserio\Component\Contract\Config\Repository
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
     * @param array $data
     *
     * @return array
     */
    protected function processParameters(array $data): array
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
     * @param mixed $parameter
     *
     * @return mixed
     */
    protected function processParameter($parameter)
    {
        foreach ($this->parameterProcessors as $processor) {
            if ($processor->supports($parameter)) {
                return $processor->process($parameter);
            }
        }

        return $parameter;
    }
}

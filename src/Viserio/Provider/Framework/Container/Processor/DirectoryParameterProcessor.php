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
    /** @var array<string, array<int, string>|string> */
    protected array $mappers;

    private CompiledContainerContract $compiledContainer;

    /**
     * Create a new DirectoryParameterProcessor instance.
     *
     * @param array<string, array<int, string>> $mappers
     */
    public function __construct(array $mappers, CompiledContainerContract $compiledContainer)
    {
        $this->compiledContainer = $compiledContainer;
        $this->mappers = $mappers;
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
            'mapper' => ['array', 'string'],
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
            throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, no mapper was found.', $parameter));
        }

        if (\is_array($value)) {
            if ($this->compiledContainer->has($value[0])) {
                $newValue = $this->compiledContainer->get($value[0])->{$value[1]}();
            } else {
                throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, mapper [%s::%s] was not found.', $parameter, $value[0], $value[1]));
            }
        } elseif (\is_string($value) && $this->compiledContainer->hasParameter($value)) {
            $newValue = $this->compiledContainer->getParameter($value);
        } else {
            $newValue = $value;
        }

        if ($newValue === null) {
            throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, non-empty string was resolved [%s].', $parameter, is_class($newValue) ? \get_class($newValue) : \gettype($newValue)));
        }

        if ($newValue === null) {
            return $parameter;
        }

        return \str_replace($search, $newValue, $parameter);
    }
}

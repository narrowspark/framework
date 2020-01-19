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

use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Processor\AbstractParameterProcessor;
use Viserio\Contract\Container\CompiledContainer;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

final class DirectoryParameterProcessor extends AbstractParameterProcessor implements RequiresComponentConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract
{
    use ContainerAwareTrait;

    /** @var array<string, array<int, string>> */
    protected array $mappers;

    /**
     * Check to active or deactivate strict parameter resolving.
     *
     * @var bool
     */
    private bool $strict;

    /**
     * Create a new DirectoryParameterProcessor instance.
     *
     * @param array<string, array<int, string>> $mappers
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(array $mappers, ContainerInterface $container)
    {
        $this->container = $container;
        $this->mappers = $mappers;

        if ($container instanceof CompiledContainer && $container->hasParameter($key = 'config.directory.processor.check_strict')) {
            $this->strict = $container->getParameter($key);
        } else {
            $this->strict = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'app', 'directory'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'mapper',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'mapper' => ['array'],
        ];
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

        $value = $this->resolvedOptions['mapper'][$key] ?? null;

        if ($value === null) {
            if ($this->strict) {
                throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, no mapper was found.', $parameter));
            }

            return $parameter;
        }

        $newValue = null;

        if ($this->container instanceof CompiledContainer) {
            $newValue = $this->container->hasParameter($value[0]) ? $this->container->getParameter($value[0])->{$value[1]}() : null;
        }

        if ($newValue === null) {
            $newValue = $this->container->has($value[0]) ? $this->container->get($value[0])->{$value[1]}() : null;
        }

        if ($this->strict && $newValue === null) {
            throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, mapper [%s::%s] was not found.', $parameter, $value[0], $value[1]));
        }

        if ($newValue === null) {
            return $parameter;
        }

        return \str_replace($search, $value, $parameter);
    }
}

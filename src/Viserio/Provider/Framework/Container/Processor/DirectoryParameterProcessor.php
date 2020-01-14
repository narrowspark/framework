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

namespace Viserio\Component\Foundation\Config\Processor;

use ArrayAccess;
use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Processor\AbstractParameterProcessor;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Container\CompiledContainer;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;

final class DirectoryParameterProcessor extends AbstractParameterProcessor
{
    use OptionsResolverTrait;
    use ContainerAwareTrait;

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Check to active or deactivate strict parameter resolving.
     *
     * @var bool
     */
    private $strict;

    /**
     * Create a new DirectoryParameterProcessor instance.
     *
     * @param array|ArrayAccess                 $config
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct($config, ContainerInterface $container)
    {
        $this->container = $container;

        if ($container instanceof CompiledContainer && $container->hasParameter($key = 'config.directory.processor.check_strict')) {
            $this->strict = $container->getParameter($key);
        } else {
            $this->strict = true;
        }
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
    public function process(string $data)
    {
        [$key,, $search] = $this->getData($data);

        $value = $this->resolvedOptions['mapper'][$key] ?? null;

        if ($value === null) {
            if ($this->strict) {
                throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, no mapper was found.', $data));
            }

            return $data;
        }

        $newValue = null;

        if ($this->container instanceof CompiledContainer) {
            $newValue = $this->container->hasParameter($value[0]) ? $this->container->getParameter($value[0])->{$value[1]}() : null;
        }

        if ($newValue === null) {
            $newValue = $this->container->has($value[0]) ? $this->container->get($value[0])->{$value[1]}() : null;
        }

        if ($this->strict && $newValue === null) {
            throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, mapper [%s::%s] was not found.', $data, $value[0], $value[1]));
        }

        if ($newValue === null) {
            return $data;
        }

        return $this->replaceData(
            $data,
            $parameterKey,
            (string) $newValue
        );
    }
}

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
use Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Config\Exception\InvalidArgumentException;
use Viserio\Contract\Container\CompiledContainer;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

final class DirectoryProcessor extends AbstractParameterProcessor implements RequiresComponentConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract
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
     * Create a new DirectoryProcessor instance.
     *
     * @param array|ArrayAccess                 $config
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct($config, ContainerInterface $container)
    {
        $this->resolvedOptions = self::resolveOptions($config);
        $this->container = $container;

        $key = 'config.directory.processor.check_strict';

        if ($container instanceof CompiledContainer && $container->hasParameter($key)) {
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
        return ['viserio', 'config', 'processor', self::getReferenceKeyword()];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return ['mapper'];
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
    public static function getReferenceKeyword(): string
    {
        return 'directory';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $data)
    {
        $parameterKey = $this->parseParameter($data);
        $parameterKeyValue = $this->resolvedOptions['mapper'][$parameterKey] ?? null;

        if ($parameterKeyValue === null) {
            if ($this->strict) {
                throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, no mapper was found.', $data));
            }

            return $data;
        }

        $newValue = null;

        if ($this->container instanceof CompiledContainer) {
            $newValue = $this->container->hasParameter($parameterKeyValue[0]) ? $this->container->getParameter($parameterKeyValue[0])->{$parameterKeyValue[1]}() : null;
        }

        if ($newValue === null) {
            $newValue = $this->container->has($parameterKeyValue[0]) ? $this->container->get($parameterKeyValue[0])->{$parameterKeyValue[1]}() : null;
        }

        if ($this->strict && $newValue === null) {
            throw new InvalidArgumentException(\sprintf('Resolving of [%s] failed, mapper [%s::%s] was not found.', $data, $parameterKeyValue[0], $parameterKeyValue[1]));
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

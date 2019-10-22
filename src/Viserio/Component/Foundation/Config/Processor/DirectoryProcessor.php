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

use Psr\Container\ContainerInterface;
use Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Container\CompiledContainer;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

final class DirectoryProcessor extends AbstractParameterProcessor implements RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract,
    RequiresValidatedConfigContract
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
     * Create a new DirectoryProcessor instance.
     *
     * @param array|\ArrayAccess                $config
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct($config, ContainerInterface $container)
    {
        $this->resolvedOptions = self::resolveOptions($config);
        $this->container = $container;
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
            return $data;
        }

        if ($this->container instanceof CompiledContainer) {
            $newValue = $this->container->hasParameter($parameterKeyValue[0]) ? $this->container->getParameter($parameterKeyValue[0])->{$parameterKeyValue[1]}() : null;
        } else {
            $newValue = $this->container->has($parameterKeyValue[0]) ? $this->container->get($parameterKeyValue[0])->{$parameterKeyValue[1]}() : null;
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

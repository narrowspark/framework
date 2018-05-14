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

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\AbstractRecursivePipe;
use Viserio\Component\OptionsResolver\Container\Definition\DimensionsOptionDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\Exception\NotFoundException;

class ResolveOptionDefinitionPipe extends AbstractRecursivePipe
{
    /** @var string */
    private $configServiceId;

    /** @var array */
    private static $configResolverCache = [];

    /**
     * Create a new ResolveOptionDefinition instance.
     *
     * @param string $configServiceId
     */
    public function __construct(string $configServiceId = RepositoryContract::class)
    {
        $this->configServiceId = $configServiceId;
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if (! $this->containerBuilder->has($this->configServiceId)) {
            throw new NotFoundException($this->configServiceId);
        }

        $isOptionDefinition = $value instanceof OptionDefinition;
        $isDimensionsOptionDefinition = $value instanceof DimensionsOptionDefinition;

        if ($isOptionDefinition || $isDimensionsOptionDefinition) {
            $value::$configClass = $value->getClass();

            if ($isOptionDefinition) {
                $name = $value->getName();
            } else {
                $name = \implode('.', $value->getClassDimensions());
            }

            $key = $value->getClass() . $name;

            if (! isset(self::$configResolverCache[$key])) {
                self::$configResolverCache[$key] = (new ReferenceDefinition(RepositoryContract::class))
                    ->addMethodCall('get', [$name]);
            }

            return self::$configResolverCache[$key];
        }

        return parent::processValue($value, $isRoot);
    }
}

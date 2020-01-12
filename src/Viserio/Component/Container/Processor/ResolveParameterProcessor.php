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

namespace Viserio\Component\Container\Processor;

use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\Exception\RuntimeException;

class ResolveParameterProcessor extends AbstractParameterProcessor
{
    /**
     * A compiled container instance.
     *
     * @var null|\Viserio\Contract\Container\CompiledContainer
     */
    protected $container;

    /**
     * Create a new ResolveParameterProcessor instance.
     *
     * @param \Viserio\Contract\Container\CompiledContainer $container
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
    public static function getProvidedTypes(): array
    {
        return ['resolve' => 'string'];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key, $process] = \explode('|', $parameter);

        \preg_match(self::PARAMETER_REGEX, $key, $match);

        $key = $match[1] ?? $key;
        $value = $this->container->getParameter($key);

        if (! \is_scalar($value)) {
            throw new RuntimeException(\sprintf('Parameter [%s] found when resolving env var [%s] must be scalar, [%s] given.', $key, $parameter, \gettype($value)));
        }

        return \str_replace(($match[0] ?? $key) . '|' . $process, $value, $parameter);
    }
}

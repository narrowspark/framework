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

use Viserio\Component\Container\Pipeline\ResolveParameterPlaceHolderPipe;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;

class ResolveParameterProcessor extends AbstractParameterProcessor
{
    use ContainerAwareTrait;

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

        \preg_match(ResolveParameterPlaceHolderPipe::REGEX, $key, $match);

        if (! isset($match[1])) {
            return '{';
        }

        $value = $this->container->getParameter($match[1]);

        if (! \is_scalar($value)) {
            throw new RuntimeException(\sprintf('Parameter [%s] found when resolving env var [%s] must be scalar, [%s] given.', $match[1], $parameter, \gettype($value)));
        }

        return \str_replace($match[0] . '|' . $process, $value, $parameter);
    }
}

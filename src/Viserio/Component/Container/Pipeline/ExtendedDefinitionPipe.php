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

namespace Viserio\Component\Container\Pipeline;

use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Pipe as PipeContract;

/**
 * @internal
 */
final class ExtendedDefinitionPipe implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            foreach ($containerBuilder->getExtenders($id) as $extender) {
                $extender($definition, $containerBuilder);
            }
        }

        foreach ($containerBuilder->getParameters() as $id => $definition) {
            foreach ($containerBuilder->getExtenders($id) as $extender) {
                $extender($definition, $containerBuilder);
            }
        }
    }
}

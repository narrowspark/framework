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

namespace Viserio\Component\Container\Test\Pipeline;

use Psr\Container\ContainerInterface;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Pipe as PipeContract;

class TestContainerPipe implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            if ($id === ContainerInterface::class) {
                continue;
            }

            if (! $definition->isPublic()) {
                $definition->setPublic(true);
            }
        }

        foreach ($containerBuilder->getAliases() as $alias => $definition) {
            if ($definition->getName() === ContainerInterface::class) {
                continue;
            }

            if (! $definition->isPublic()) {
                $definition->setPublic(true);
            }
        }
    }
}

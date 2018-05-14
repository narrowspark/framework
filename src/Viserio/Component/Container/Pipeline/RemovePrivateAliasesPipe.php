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

namespace Viserio\Component\Container\Pipeline;

use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Pipe as PipeContract;

/**
 * @internal
 */
final class RemovePrivateAliasesPipe implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        foreach ($containerBuilder->getAliases() as $id => $alias) {
            if ($alias->isPublic()) {
                continue;
            }

            $containerBuilder->removeAlias($id);
            $containerBuilder->log($this, \sprintf('Removed service "%s"; reason: private alias.', $id));
        }
    }
}

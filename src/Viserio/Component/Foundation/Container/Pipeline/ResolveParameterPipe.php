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

namespace Viserio\Component\Foundation\Container\Pipeline;

use Viserio\Component\Config\Container\Pipeline\ResolveParameterPipe as BaseResolveParameterPipe;
use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

class ResolveParameterPipe extends BaseResolveParameterPipe
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->processors[] = $containerBuilder->getDefinition(ComposerExtraProcessor::class)->getValue();
        $this->processors[] = $containerBuilder->getDefinition(DirectoryProcessor::class)->getValue();

        parent::process($containerBuilder);
    }
}

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

namespace Viserio\Component\Foundation\Container\Provider;

use Viserio\Component\Container\Pipeline\UnusedTagsPipe;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider;

class FoundationServiceProvider implements PipelineServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            'afterRemoving' => [
                [
                    new UnusedTagsPipe([
                        'console.command',
                        'container.preload',
                        'monolog.logger',
                        'proxy',
                        'translation.dumper',
                        'translation.extractor',
                        'translation.loader',
                        'twig.extension',
                        'twig.loader',
                    ]),
                ],
            ],
        ];
    }
}

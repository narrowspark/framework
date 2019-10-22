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

namespace Viserio\Contract\Container\ServiceProvider;

interface PipelineServiceProvider
{
    /**
     * Returns a list of all pipelines that will configure the container.
     *
     * - the key is the pipeline type
     *      - 'afterRemoving'
     *      - 'beforeOptimization'
     *      - 'beforeRemoving'
     *      - 'optimization'
     *      - 'removing'
     *
     * - the value is a array
     *      - the key is the priority
     *      - the value is a array containing the pipeline object
     *
     * @example [
     *      'afterRemoving' => [
     *          10 => [
     *              new CustomPipe
     *          ]
     *      ]
     * ]
     *
     * @return array
     */
    public function getPipelines(): array;
}

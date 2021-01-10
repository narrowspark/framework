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
     * @return array<int, <array<int, \Viserio\Contract\Container\Pipe>>
     */
    public function getPipelines(): array;
}
